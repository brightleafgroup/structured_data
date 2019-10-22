<?php

namespace Drupal\structured_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\structured_data\Core\Helper;

/**
 * Class PageJsonForm.
 *
 * @package Drupal\structured_data\Form
 */
class PageJsonForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'structured_data_page_json_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();

    $form['route_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route Name'),
      '#required' => TRUE,
      '#default_value' => $entity->route_name,
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#required' => FALSE,
      '#default_value' => $entity->url,
    ];

    $form['bundle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bundle'),
      '#required' => FALSE,
      '#default_value' => $entity->bundle === Helper::EMPTY_BUNDLE ? '' : $entity->bundle,
    ];

    $form['entity_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Entity Id'),
      '#required' => FALSE,
      '#default_value' => $entity->entity_id == '0' ? '' : $entity->entity_id,
    ];

    $form['json'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Json'),
      '#required' => FALSE,
      '#default_value' => $entity->json,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = [
      'route_name' => $form_state->getValue('route_name'),
      'url' => $form_state->getValue('url'),
      'bundle' => $form_state->getValue('bundle'),
      'entity_id' => $form_state->getValue('entity_id'),
      'json' => $form_state->getValue('json'),
      'updated_by' => $this->currentUser()->id(),
      'updated_time' => time(),
    ];

    Helper::updatePageJson($entity);

    $this->messenger()->addMessage($this->t('Page Json updated successfully.'));

    if (!empty($entity['url'])) {
      $url = Url::fromUri('internal:' . $entity['url']);
      $form_state->setRedirectUrl($url);
    }
  }

  /**
   * Get entity details from current route.
   *
   * @return mixed|\stdClass
   *   Entity details.
   */
  private function getEntity() {
    $route_name = $this->getRouteMatch()->getParameter('sd_route_name');
    $url = $this->getRouteMatch()->getParameter('sd_url');
    $bundle = $this->getRouteMatch()->getParameter('sd_bundle');
    $entity_id = $this->getRouteMatch()->getParameter('sd_entity_id');

    $url = str_replace('|', '/', $url);
    $url = base64_decode($url);

    $entity = Helper::getPageJson([
      'route_name' => $route_name,
      'url' => $url,
      'bundle' => $bundle,
      'entity_id' => $entity_id,
    ]);

    if (empty($entity)) {
      $entity = new \stdClass();
      $entity->route_name = $route_name;
      $entity->url = $url;
      $entity->bundle = $bundle;
      $entity->entity_id = $entity_id;
      $entity->json = '';
    }

    return ($entity);
  }

}
