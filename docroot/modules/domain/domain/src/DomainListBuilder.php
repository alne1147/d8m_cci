<?php

namespace Drupal\domain;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\domain\DomainAccessControlHandler;
use Drupal\domain\DomainLoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * User interface for the domain overview screen.
 */
class DomainListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  protected $entitiesKey = 'domains';

  /**
   * The current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The redirect destination helper.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $destinationHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The domain entity access control handler.
   *
   * @var \Drupal\domain\DomainAccessControlHandler
   */
  protected $accessHandler;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The domain loader.
   *
   * @var \Drupal\domain\DomainLoaderInterface
   */
  protected $domainLoader;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('current_user'),
      $container->get('redirect.destination'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('domain.loader')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The active user account.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $destination
   *   The redirect destination helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\domain\DomainLoaderInterface $domain_loader
   *   The domain loader.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, AccountInterface $account, RedirectDestinationInterface $destination_handler, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, DomainLoaderInterface $domain_loader) {
    parent::__construct($entity_type, $storage);
    $this->entityTypeId = $entity_type->id();
    $this->storage = $storage;
    $this->entityType = $entity_type;
    $this->currentUser = $account;
    $this->destinationHandler = $destination_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->accessHandler = $this->entityTypeManager->getAccessControlHandler('domain');
    $this->moduleHandler = $module_handler;
    $this->domainLoader = $domain_loader;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'domain_admin_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $destination = $this->destinationHandler->getAsArray();
    $default = $entity->isDefault();
    $id = $entity->id();

    // If the user cannot edit domains, none of these actions are permitted.
    $access = $this->accessHandler->checkAccess($entity, 'update');
    if ($access->isForbidden()) {
      return $operations;
    }

    $super_admin = $this->currentUser->hasPermission('administer domains');
    if ($super_admin || $this->currentUser->hasPermission('access inactive domains')) {
      if ($entity->status() && !$default) {
        $operations['disable'] = array(
          'title' => $this->t('Disable'),
          'url' => Url::fromRoute('domain.inline_action', array('op' => 'disable', 'domain' => $id)),
          'weight' => 50,
        );
      }
      elseif (!$default) {
        $operations['enable'] = array(
          'title' => $this->t('Enable'),
          'url' => Url::fromRoute('domain.inline_action', array('op' => 'enable', 'domain' => $id)),
          'weight' => 40,
        );
      }
    }
    if (!$default && $super_admin) {
      $operations['default'] = array(
        'title' => $this->t('Make default'),
        'url' => Url::fromRoute('domain.inline_action', array('op' => 'default', 'domain' => $id)),
        'weight' => 30,
      );
    }
    if (!$default && $this->accessHandler->checkAccess($entity, 'delete')->isAllowed()) {
      $operations['delete'] = array(
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('entity.domain.delete_form', array('domain' => $id)),
        'weight' => 20,
      );
    }
    $operations += $this->moduleHandler->invokeAll('domain_operations', array($entity, $this->currentUser));
    foreach ($operations as $key => $value) {
      if (isset($value['query']['token'])) {
        $operations[$key]['query'] += $destination;
      }
    }
    /** @var DomainInterface $default */
    $default = $this->domainLoader->loadDefaultDomain();

    // Deleting the site default domain is not allowed.
    if ($id == $default->id()) {
      unset($operations['delete']);
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['hostname'] = $this->t('Hostname');
    $header['status'] = $this->t('Status');
    $header['is_default'] = $this->t('Default');
    $header += parent::buildHeader();
    if (!$this->currentUser->hasPermission('administer domains')) {
      unset($header['weight']);
    }
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // If the user cannot view the domain, none of these actions are permitted.
    $admin = $this->accessHandler->checkAccess($entity, 'view');
    if ($admin->isForbidden()) {
      return;
    }

    $row['label'] = $this->getLabel($entity);
    $row['hostname'] = array('#markup' => $entity->getLink());
    if ($entity->isActive()) {
      $row['hostname']['#prefix'] = '<strong>';
      $row['hostname']['#suffix'] = '</strong>';
    }
    $row['status'] = array('#markup' => $entity->status() ? $this->t('Active') : $this->t('Inactive'));
    $row['is_default'] = array('#markup' => ($entity->isDefault() ? $this->t('Yes') : $this->t('No')));
    $row += parent::buildRow($entity);

    if (!$this->currentUser->hasPermission('administer domains')) {
      unset($row['weight']);
    }
    else {
      $row['weight']['#delta'] = count($this->domainLoader->loadMultiple()) + 1;
    }
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form[$this->entitiesKey]['#domains'] = $this->entities;
    $form['actions']['submit']['#value'] = $this->t('Save configuration');
    // Only super-admins may sort domains.
    if (!$this->currentUser->hasPermission('administer domains')) {
      $form['actions']['submit']['#access'] = FALSE;
      unset($form['#tabledrag']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message($this->t('Configuration saved.'));
  }

}
