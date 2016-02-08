<?php

/**
 * @file
 * Contains \Drupal\node_csv_uploader\DeleteCSVForm.
 */

namespace Drupal\node_csv_uploader;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class DeleteCSVForm extends ConfirmFormBase {

    protected $id;

    function getFormID() {
        return 'node_csv_uploader.delete';
    }

    function getQuestion() {
        return t('Are you sure you want to delete (:id)?', array(':id' => $this->id));
    }

    function getConfirmText() {
        return t('Delete');
    }

    function getCancelUrl() {
        return new Url('node_csv_uploader.admin');
    }

    function buildForm(array $form, FormStateInterface $form_state, $id = '') {
        $this->id = $id;
        return parent::buildForm($form, $form_state);
    }

    function submitForm(array &$form, FormStateInterface $form_state) {
        $result = ManageStorage::fetchRowFields('id', $this->id, array('fid'));
        file_delete($result['fid']);
        ManageStorage::delete($this->id);
        drupal_set_message('CSV deleted successfully.');
        $form_state->setRedirect('node_csv_uploader.admin');
    }
}