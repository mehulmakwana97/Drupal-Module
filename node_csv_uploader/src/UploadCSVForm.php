<?php

namespace Drupal\node_csv_uploader;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\node_csv_uploader\NodeTypes;
use Drupal\node_csv_uploader\CSVFileReader;

use Drupal\node\Entity\Node;

class UploadCSVForm implements FormInterface {

  /**
   * File configuration
   */

  protected $configuration;

  protected $content_type = 'stocklists';

  public function __construct() {
    $this->configuration = $this->defaultConfiguration();
  }

  function getFormID() {
    return 'node_csv_uploader.add';
  }

  function buildForm(array $form, FormStateInterface $form_state) {
    $form['file_name'] = array(
      '#type' => 'managed_file',
      '#title' => t('Choose File'),
      '#description' => t('Upload a file, allowed extensions: '. $this->configuration['allowed_extensions']),
      '#upload_validators' => [
        'file_validate_extensions' => [
          $this->configuration['allowed_extensions'],
        ],
      ],
      '#upload_location' => $this->configuration['directory'],
      '#required' => TRUE,
    );
    /*$form['content_type'] = array(
      '#type' => 'select',
      '#title' => 'Content Type',
      '#options' => [$this->content_type => 'Stocklists']//$this->getContentTypes() != NULL ? $this->getContentTypes() : NULL
    );*/
    $form['actions'] = array(
      '#type' => 'submit',
      '#value' => t('Upload')
    );
    return $form;
  }

  function getContentTypes() {
    $types = array();
    $node = node_type_get_types();
    $node_types = new NodeTypes;
    foreach($node as $key => $value) {
      $node_types->loadNode($value);
      $types[$key] = $node_types->getName();
    }
    return $types;
  }

  function validateForm(array &$form, FormStateInterface $form_state) {

  }

  function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user = \Drupal::currentUser();
    $values = $form_state->getValues();
    $fid = $values['file_name'][0];
    $file_uri = $this->getFileUri($fid);

    $file = new CSVFileReader($file_uri[0]['value']);
    $delimiter = ',';
    $enclosure = '"';
    $escape = '\\';
    $file->setCsvControl($delimiter, $enclosure, $escape);
    $file->execute();

    $rows = $file->getRows();
    $valid_csv = false;
    foreach($rows as $key => $value) {
      $title = isset($value['title']) ? $value['title'] : '';
      $address = isset($value['address']) ? $value['address'] : '';
      if(!empty($title) AND !empty($address)) {
        $valid_csv = true;
        $google_map = new GoogleMaps();
        $map_results = $google_map->setAddress($title .', '. $address)->get();

        $node = Node::create(array(
            'type' => 'stocklists',
            'title' => $title,
            'langcode' => 'en',
            'uid' => $current_user->id(),
            'status' => 1,
            'field_latitude' => array(
              'value' => $map_results->getLatitude() != NULL ? $map_results->getLatitude() : 0,
            ),
            'field_longitude' => array(
              'value' => $map_results->getLongitude() != NULL ? $map_results->getLongitude() : 0,
            ),
            'field_country' => array(
              'value' => isset($value['country']) ? $value['country'] : '',
            ),
            'field_address' => array(
              'value' => $address,
            ),
        ));
        $node->save();
      } else {
        $valid_csv = false;
        break;
      }
    }

    if($valid_csv) {
      ManageStorage::add(array(
        'fid' => $fid,
        'content_type' => $this->content_type
      ));

      drupal_set_message(t('Your file content has been uploaded successfully.'));
      $form_state->setRedirect('node_csv_uploader.admin');
    } else {
      drupal_set_message(t('Please upload valid csv file, file format is not matched.'), 'error');
      $form_state->setRedirect('node_csv_uploader.add');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'allowed_extensions' => 'csv',//txt csv tsv xml opml
      'directory' => 'public://node_csv',
    ];
  }

  public function getFileUri($fid) {
    return file_load($fid)->uri->getValue();
  }
}