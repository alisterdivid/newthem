<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Entry_Exporter
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     * @param  Quform_Options       $options
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory, Quform_Options $options)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->options = $options;
    }

    /**
     * Handle the Ajax request to get the field choices list when exporting entries
     */
    public function getExportFieldList()
    {
        $id = isset($_POST['form_id']) ? (int) $_POST['form_id'] : 0;

        if ($id == 0) {
            wp_send_json(array('type' => 'error', 'message' => __('Bad request.')));
        }

        $config = $this->repository->getConfig($id);

        if ($config === null) {
            wp_send_json(array('type' => 'error', 'message' => __('Form not found.')));
        }

        $form = $this->factory->create($config);

        $data = array();

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element->config('saveToDatabase')) {
                $data[] = array(
                    'label' => $element->getAdminLabel(),
                    'value' => 'element_' . $element->getId()
                );
            }
        }

        foreach ($this->getCoreEntryColumns() as $key => $label) {
            $data[] = array(
                'value' => $key,
                'label' => $label
            );
        }

        wp_send_json(array(
            'type' => 'success',
            'data' => $data
        ));
    }

    /**
     * Get the list of default entry columns
     *
     * @return array
     */
    protected function getCoreEntryColumns()
    {
        return array(
            'id' => __('Entry ID', 'quform'),
            'ip' => __('IP address', 'quform'),
            'form_url' => __('Form URL', 'quform'),
            'referring_url' => __('Referring URL', 'quform'),
            'post_id' => __('Page', 'quform'),
            'created_by' => __('User', 'quform'),
            'created_at' => __('Date', 'quform'),
            'updated_at' => __('Last modified', 'quform')
        );
    }

    /**
     * Generate the file containing exported entries
     *
     * @param  Quform_Form  $form     The form to export entries from
     * @param  array        $columns  The selected columns for the export file
     * @param  array        $format   The options for the format of the export file
     * @param  string       $from     The date of the earliest entry in the format YYYY-MM-DD
     * @param  string       $to       The date of the latest entry in the format YYYY-MM-DD
     */
    public function generateExportFile(Quform_Form $form, array $columns = array(), $format = array(), $from = '', $to = '')
    {
        $entries = $this->repository->exportEntries($form, $from, $to);

        // Sanitise chosen columns
        $coreColumns = $this->getCoreEntryColumns();
        $cols = array();

        foreach ($columns as $col) {
            if (array_key_exists($col, $coreColumns)) {
                // It's a core column, get the label
                $cols[$col] = $coreColumns[$col];
            } elseif (preg_match('/element_(\d+)/', $col, $matches)) {
                // It's an element column, so get the element admin label
                $element = $form->getElementById((int) $matches[1]);
                $cols[$col] = $element instanceof Quform_Element ? $element->getAdminLabel() : '';
            }
        }

        if ( ! class_exists('PHPExcel')) {
            require_once QUFORM_LIBRARY_PATH . '/PHPExcel.php';
        }

        $phpExcel = new PHPExcel();
        $sheet = $phpExcel->getActiveSheet();

        $headingColumnCount = 0;
        foreach ($cols as $col) {
            $sheet->setCellValueByColumnAndRow($headingColumnCount++, 1, $col);
        }

        $rowCount = 2;

        // Write each entry
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                $row = array();
                $columnCount = 0;

                foreach ($cols as $col => $label) {
                    $row[$col] = isset($entry[$col]) ? $entry[$col] : '';

                    if (strlen($row[$col]) && strpos($col, 'element_') !== false) {
                        $elementId = (int) str_replace('element_', '', $col);
                        $row[$col] = $form->setValueFromStorage($elementId, $row[$col])->getValueText($elementId);
                    }

                    // Format the date to include the WordPress Timezone offset
                    if ($col == 'created_at' || $col == 'updated_at') {
                        $row[$col] = $this->options->formatDate($row[$col]);
                    }

                    $sheet->setCellValueByColumnAndRow($columnCount, $rowCount, $row[$col]);
                    $columnCount++;
                }

                $rowCount++;
            }
        }

        switch (Quform::get($format, 'type')) {
            case 'csv':
            default:
                $contentType = 'text/csv';
                $extension = '.csv';
                $writer = new PHPExcel_Writer_CSV($phpExcel);
                $writer->setExcelCompatibility((bool) Quform::get($format, 'excelCompatibility', false));
                $writer->setDelimiter(Quform::get($format, 'delimiter', ','));
                $writer->setEnclosure(Quform::get($format, 'enclosure', '"'));
                $writer->setUseBOM((bool) Quform::get($format, 'useBom', false));
                $writer->setLineEnding(Quform::get($format, 'lineEndings', "\r\n"));
                break;
            case 'xls':
                $contentType = 'application/vnd.ms-excel';
                $extension = '.xls';
                $writer = new PHPExcel_Writer_Excel5($phpExcel);
                break;
            case 'xlsx':
                $contentType = 'application/vnd.ms-excel';
                $extension = '.xlsx';
                $writer = new PHPExcel_Writer_Excel2007($phpExcel);
                break;
            case 'ods':
                $contentType = 'application/vnd.oasis.opendocument.spreadsheet';
                $extension = '.ods';
                $writer = new PHPExcel_Writer_OpenDocument($phpExcel);
                break;
            case 'html':
                $contentType = 'text/html';
                $extension = '.html';
                $writer = new PHPExcel_Writer_HTML($phpExcel);
                break;
        }


        // Send headers
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($form->config('name')) . '-' . date('Y-m-d') . $extension . '"');
        header('Cache-Control: private, must-revalidate, max-age=0');

        // Send the file contents
        $writer->save('php://output');
        exit;
    }
}
