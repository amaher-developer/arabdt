<?php

namespace Drupal\drupal_arabdt_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Our arabdt form class.
 */
class ArabDTForm extends FormBase  {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupal_arabdt_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('<h1>Custom ArabDT Form</h1>'),
    ];


    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text Input'),
      '#description' => $this->t('text input that must be more than 9 characters'),
      '#required' => TRUE,
    ];
    $form['file'] = [
      '#title' => $this->t('Upload file'),
      '#type' => 'file',
      '#description' => $this->t('5 MB limit.'),
//      '#upload_location' => 'private://',
      '#multiple' => FALSE,
      '#upload_location' => 'public://uploads/',
      '#progress_message' => $this->t('Please wait...'),
      '#size' => array(5485760),
    ];
    $form['select'] = [
      '#type' => 'select',
      '#options' => [
        '1' => $this
          ->t('First'),
        '2' => $this
          ->t('Second'),
        '3' => $this
          ->t('Three'),
      ],
      '#title' => $this->t('Select'),
      '#required' => TRUE,
    ];

    $form['accept'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('CheckBox'),
    );


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $title = $form_state->getValue('title');
    $select = $form_state->getValue('select');
    $all_files = \Drupal::request()->files->get('files', array());

    if (empty($all_files['file'])) {
      $form_state->setErrorByName('file', $this->t('Upload file correctly.'));
    }else{
//      $this->file_move($all_files['file'], 'uploads/');
    }

    if (strlen($title) < 10) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 10 characters long.'));
    }


    if (empty($select)){
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('select', $this->t('You choose from select'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Display the results.

    // Call the Static Service Container wrapper
    // We should inject the messenger service, but its beyond the scope of this example.
    $messenger = \Drupal::messenger();
    $messenger->addMessage('Title: '.$form_state->getValue('title'));
    $messenger->addMessage('Select: '.$form_state->getValue('select'));
    $messenger->addMessage('Checkbox: '.$form_state->getValue('accept'));
//    $messenger->addMessage('File: '.$form_state->getValue('file'));

    // Redirect to home
    $form_state->setRedirect('drupal_arabdt_form.arabdt_form');

  }

  function file_move(FileInterface $source, $destination = NULL, $replace = FILE_EXISTS_RENAME) {
    if (!file_valid_uri($destination)) {
      if (($realpath = drupal_realpath($source
          ->getFileUri())) !== FALSE) {
        \Drupal::logger('file')
          ->notice('File %file (%realpath) could not be moved because the destination %destination is invalid. This may be caused by improper use of file_move() or a missing stream wrapper.', array(
            '%file' => $source
              ->getFileUri(),
            '%realpath' => $realpath,
            '%destination' => $destination,
          ));
      }
      else {
        \Drupal::logger('file')
          ->notice('File %file could not be moved because the destination %destination is invalid. This may be caused by improper use of file_move() or a missing stream wrapper.', array(
            '%file' => $source
              ->getFileUri(),
            '%destination' => $destination,
          ));
      }
      drupal_set_message(t('The specified file %file could not be moved because the destination is invalid. More information is available in the system log.', array(
        '%file' => $source
          ->getFileUri(),
      )), 'error');
      return FALSE;
    }
    if ($uri = file_unmanaged_move($source
      ->getFileUri(), $destination, $replace)) {
      $delete_source = FALSE;
      $file = clone $source;
      $file
        ->setFileUri($uri);

      // If we are replacing an existing file re-use its database record.
      if ($replace == FILE_EXISTS_REPLACE) {
        $existing_files = entity_load_multiple_by_properties('file', array(
          'uri' => $uri,
        ));
        if (count($existing_files)) {
          $existing = reset($existing_files);
          $delete_source = TRUE;
          $file->fid = $existing
            ->id();
          $file->uuid = $existing
            ->uuid();
        }
      }
      elseif ($replace == FILE_EXISTS_RENAME && is_file($destination)) {
        $file
          ->setFilename(drupal_basename($destination));
      }
      $file
        ->save();

      // Inform modules that the file has been moved.
      \Drupal::moduleHandler()
        ->invokeAll('file_move', array(
          $file,
          $source,
        ));

      // Delete the original if it's not in use elsewhere.
      if ($delete_source && !\Drupal::service('file.usage')
          ->listUsage($source)) {
        $source
          ->delete();
      }
      return $file;
    }
    return FALSE;
  }
}
