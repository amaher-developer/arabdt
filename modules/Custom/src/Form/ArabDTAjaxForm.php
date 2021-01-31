<?php

namespace Drupal\drupal_arabdt_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Our arabdt form class.
 */
class ArabDTAjaxForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupal_arabdt_ajax_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->messenger->addMessage('Title: '.$form_state->getValue('title'));
    $this->messenger->addMessage('Accept: '.$form_state->getValue('accept'));
//    $form['massage'] = [
//      '#type' => 'markup',
//      '#markup' => '<div class="result_message"></div>',
//    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Enter the title.'),
      '#minlength' => 9,
      '#required' => true,
    ];

    $form['file_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('File Upload'),
    ];

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::setMessage',
      ]
    ];

    return $form;
  }

  public function setMessage(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $response->addCommand(
      new HtmlCommand(
        '.result_message',
//        '<div class="my_top_message">' . $this->t('The form added successfully @result', ['@result' => ($form_state->getValue('number_1') + $form_state->getValue('number_2'))])
        '<div class="my_top_message">' . $this->t('The form added successfully ')
      )
    );

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
