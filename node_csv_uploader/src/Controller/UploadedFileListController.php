<?php

/**
 * @file
 * Contains \Drupal\node_csv_uploader\Controller\UploadedFileListController.
 */

namespace Drupal\node_csv_uploader\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node_csv_uploader\ManageStorage;
use Drupal\node_csv_uploader\GoogleMaps;

class UploadedFileListController extends ControllerBase {

    public function __construct() {

    }

    public function contentOverview() {
        // Table Header
        $header = array(
            'id' => array(
                'data' => t('Id'),
            ),
            'file_name' => array(
                'data' => t('File Name'),
            ),
            'content_type' => array(
                'data' => t('Content Type'),
            ),
            'operations' => array(
                'data' => t('Delete'),
            ),
        );

        $rows = array();
        $count = 1;
        foreach(ManageStorage::getAll() as $key => $content) {
            $file = file_load($content->fid);
            $file_link = '';
            if($file !== NULL) {
                $file_name_value = $file->filename->getvalue();
                $file_name_part = explode('.', $file_name_value[0]['value']);
                $file_name = $file_name_part[0];

                $file_uri = $file->uri->getvalue();
                $file_link = \Drupal::l(t($file_name), Url::fromUri(file_create_url($file_uri[0]['value'])));
            }
            $rows[$key] = array(
                'data' => array($count, $file_link, $content->content_type, \Drupal::l(t('Delete'), Url::fromRoute('node_csv_uploader.delete', ['id' => $content->id])))
            );
            $count++;
        }

        $table = array(
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#attributes' => array(
                'id' => 'upload-node-csv'
            ),
            '#empty' => $this->t('No uploaded csv file available.')
        );

        return $table;
    }
}