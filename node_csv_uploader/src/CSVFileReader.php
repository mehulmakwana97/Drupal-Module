<?php

/**
 * @file
 * Contains \Drupal\node_csv_uploader\CSVFileReader.
 */

namespace Drupal\node_csv_uploader;

/**
 * Defines a CSV file object.
 *
 * @package Drupal\migrate_source_csv.
 *
 * Extends SPLFileObject to:
 * - assume CSV format
 * - skip header rows on rewind()
 * - address columns by header row name instead of index.
 */
class CSVFileReader extends \SplFileObject {

  /**
   * The number of rows in the CSV file before the data starts.
   *
   * @var integer
   */
  protected $headerRowCount = 0;

  /**
   * The human-readable column headers, keyed by column index in the CSV.
   *
   * @var array
   */
  protected $columnNames = [];

  /**
   * The human-readable column headers in the CSV.
   *
   * @var array
   */
  protected $headers = [];

  /**
   * The human-readable rows data in the CSV.
   *
   * @var array
   */
  protected $rows = [];

  /**
   * {@inheritdoc}
   */
  public function __construct($file_name) {
    // Necessary to use this approach because SplFileObject doesn't like NULL
    // arguments passed to it.
    call_user_func_array(['parent', '__construct'], func_get_args());

    $this->setFlags(CSVFileReader::READ_CSV | CSVFileReader::READ_AHEAD | CSVFileReader::DROP_NEW_LINE | CSVFileReader::SKIP_EMPTY);
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->seek($this->getHeaderRowCount());
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    $row = parent::current();

    if ($row && !empty($this->columnNames)) {
      // Only use columns specified in the defined CSV columns.
      $row = array_intersect_key($row, $this->columnNames);
      // Set meaningful keys for the columns mentioned in $this->csvColumns.
      foreach ($this->columnNames as $key => $value) {
        // Copy value to more descriptive key and unset original.
        $value = key($value);
        $row[$value] = isset($row[$key]) ? $row[$key] : NULL;
        unset($row[$key]);
      }
    }

    return $row;
  }

  /**
   * Return a count of all available source records.
   */
  public function count() {
    return iterator_count($this);
  }

  /**
   * Number of header count.
   *
   * @return int
   *   Get the number of header count, zero if no header row.
   */
  public function getHeaderRowCount() {
    return $this->headerRowCount;
  }

  /**
   * Number of header names.
   * @return array
   *  Get header names, empty if no header row.
   */

  public function getHeaderNames() {
    return $this->headers;
  }

  /**
   * All rows data except header
   * @return array
   *  Get rows data.
   */

  public function getRows() {
    return $this->rows;
  }

  /**
   * Execute and get all headers, and header rows data.
   */

  public function execute() {
    if($this->count() > 0) {
      for ($i = 0; $i <$this->count() ; $i++) {
        $this->rewind();
        $this->seek($i);
        $row = $this->current();
        $each_rows = [];
        foreach($row as $key => $data) {
          $data = trim($data);
          if($i == 0) {
            $this->headers[] = $data;
          } else {
            $each_rows[strtolower($this->headers[$key])] = $data;
          }
        }
        if($i > 0)
          $this->rows[] = $each_rows;
      }
      $this->headerRowCount = count($this->getHeaderNames());
    }
  }
}
