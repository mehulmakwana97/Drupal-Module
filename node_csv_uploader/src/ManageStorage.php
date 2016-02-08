<?php

/**
 * @file
 * Contains \Drupal\node_csv_uploader\ManageStorage.
 */

namespace Drupal\node_csv_uploader;

class ManageStorage {

    private static function getTable() {
        return 'node_csv_uploader';
    }

    static function getAll() {
        $result = db_query("SELECT * FROM {". ManageStorage::getTable() ."}")->fetchAllAssoc('id');
        return $result;
    }

    static function exists($fid, $content_type) {
        $result = db_query("SELECT 1 FROM {". ManageStorage::getTable() ."} WHERE fid = :fid AND content_type = :content_type",
                        array(':fid' => $fid, ':content_type' => $content_type)
                    )->fetchField();
        return (bool) $result;
    }

    static function add(array $fields) {
        if(isset($fields))
            db_insert(ManageStorage::getTable())->fields($fields)->execute();
    }

    static function delete($id) {
        if(!empty($id)) {
            db_delete(ManageStorage::getTable())->condition('id', $id)->execute();
        }
    }

    static function fetchRowFields($field, $where, $fields = array()) {
        $result = db_select(ManageStorage::getTable(), 'csv')
                    ->fields('csv', $fields)
                    ->condition($field, $where)
                    ->execute()
                    ->fetchAssoc();

        return $result;
    }
}