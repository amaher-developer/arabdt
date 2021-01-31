<?php

namespace Drupal\drupal_arabdt_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
      '#markup' => $this->t('Please enter the title and accept the terms of use of the site.'),
    ];


    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Enter the title of the book. Note that the title must be at least 10 characters in length.'),
      '#required' => TRUE,
    ];
    $form['file'] = [
      '#title' => $this->t('Upload file'),
      '#type' => 'file',
      '#description' => $this->t('Upload file'),
      '#required' => TRUE,
//      '#upload_location' => 'private://',
      '#multiple' => FALSE
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
      '#title' => $this->t('Title'),
      '#description' => $this->t('Enter the title of the book. Note that the title must be at least 10 characters in length.'),
      '#required' => TRUE,
    ];

    $form['accept'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('I accept the terms of use of the site'),
      '#description' => $this->t('Please read and accept the terms of use'),
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
    $accept = $form_state->getValue('accept');
    $file = $form_state->getValue('file');
    $all_files = $this->getRequest()->files->get('file');
    if (!empty($all_files['file'])) {
      $file_upload = $all_files['file'];
      if ($file_upload->isValid()) {
        $form_state->setValue('file', $file_upload->getRealPath());
      }
    }else
      $form_state->setErrorByName('file', $this->t('The file could not be uploaded.'));


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
    $messenger->addMessage('Accept: '.$form_state->getValue('accept'));

    // Redirect to home
    $form_state->setRedirect('<front>');

  }

}
