<?php

namespace Drupal\onovas_web_speech\Form\config;

/**
 * @file
 * SettingsForm.php
 */

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\onovas_web_speech\lib\general\MarkdownParser;

/**
 * Formulario de configuración del módulo.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $pathResolver;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $typeManager;

  /**
   * Constructor para añadir dependencias.
   *
   * @param \Drupal\Core\Extension\ExtensionPathResolver $logger
   *   Servicio PathResolver.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $type_manager
   *   Servicio EntityTypeManager.
   */
  public function __construct(ExtensionPathResolver $path_resolver,
                              EntityTypeManagerInterface $type_manager) {
    $this->pathResolver = $path_resolver;
    $this->typeManager = $type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.path.resolver'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Implements getFormId().
   */
  public function getFormId() {
    return 'onovas_web_speech.settings';
  }

  /**
   * Implements getEditableConfigNames().
   */
  protected function getEditableConfigNames() {
    return ['onovas_web_speech.settings'];
  }

  /**
   * Implements buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* Obtengo la configuración actual */
    /* $config = \Drupal::configFactory()->getEditable('custom_module.onovas_web_speech.settings'); */
    $config = $this->config('onovas_web_speech.settings');

    /* SETTINGS FORM */
    $form['settings'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['general_settings'] = $this->getGeneralSettings($config);
    $form['general_settings']['#open'] = TRUE;

    $form['entities_settings'] = $this->getEntitiesSettings($config);

    /* *************************************************************************
     * CONTENIDO DE CHANGELOG.md, LICENSE.md y README.md
     * ************************************************************************/

    /* Datos auxiliares */
    $module_path = $this->pathResolver
      ->getPath('module', "onovas_web_speech");

    /* Compruebo si existe y leo el contenido del archivo CHANGELOG.md */
    $contenido = $this->getChangeLogBuild($config, $module_path);
    if ($contenido) {
      $form['info'] = $contenido;
    }

    /* Compruebo si existe y leo el contenido del archivo LICENSE.md */
    $contenido = $this->getLicenseBuild($config, $module_path);
    if ($contenido) {
      $form['license'] = $contenido;
    }

    /* Compruebo si existe y leo el contenido del archivo README.md */
    $contenido = $this->getReadmeBuild($config, $module_path);
    if ($contenido) {
      $form['help'] = $contenido;
    }

    /* Librería para obtener y probar el speech */
    $form['#attached']['library'][] = 'onovas_web_speech/speech_test';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $errors = $form_state->getErrors();
    if (count($errors) == 1 && $errors['voice']) {
      $form_state->clearErrors();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('onovas_web_speech.settings');

    /* Los campos a guardar */
    $list = [
      'rate',
      'pitch',
      'voice',
      'container',

      'content_types',
    ];

    foreach ($list as $item) {
      $config->set($item, $form_state->getValue($item));
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Genera el formulario para la configuración general del módulo.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getGeneralSettings(Config $config): array {
    $form['general_settings'] = [
      '#type'        => 'details',
      '#title'       => $this->t('General'),
      '#group'       => 'settings',
      '#description' => $this->t('<p><h2>General Settings</h2></p>'),

      'voice_container' => [
        '#type'  => 'fieldset',
        '#title' => $this->t('Voice'),

        'voice' => [
          '#type'          => 'select',
          '#title'         => $this->t('Voices'),
          '#default_value' => $config->get('voice') ?? '_none',
          '#required'      => TRUE,
          '#options'       => [],
          '#empty_option'  => $this->t('-- None --'),
          '#empty_value'   => '_none',
          '#multiple'      => FALSE,
          '#suffix'        => '<div id="voice-info" class="visually-hidden">' .
                              ($config->get('voice') ?? '_none') .
                              '</div>'
        ],

        'rate' => [
          '#type'          => 'range',
          '#title'         => $this->t('Rate'),
          '#default_value' => $config->get('rate') ?? 1,
          '#step'          => 0.1,
          '#min'           => 0.5,
          '#max'           => 2,
          '#required'      => TRUE,
        ],

        'pitch' => [
          '#type'          => 'range',
          '#title'         => $this->t('Pitch'),
          '#default_value' => $config->get('pitch') ?? 1,
          '#step'          => 0.1,
          '#min'           => 0,
          '#max'           => 2,
          '#required'      => TRUE,
        ],
      ],
      'container_settings' => [
        '#type'  => 'fieldset',
        '#title' => $this->t('Container Settings'),

        'container' => [
          '#type'          => 'textfield',
          '#title'         => $this->t('Container'),
          '#description'   => $this->t('CSS class / Id of the container to read.<br>Examples: .container, #main, .main-content'),
          '#default_value' => $config->get('container') ?? '',
          '#required'      => TRUE,
        ],
      ],

      'test_container' => [
        '#type'  => 'fieldset',
        '#title' => $this->t('Tests'),

        'sample_text' => [
          '#type'          => 'textfield',
          '#title'         => $this->t('Sample text'),
          '#default_value' => $this->t('Hi, my name is Óscar'),
          '#required'      => TRUE,
        ],

        'actions' => [
          '#type' => 'actions',

          'btn_test' => [
            '#type'       => 'submit',
            '#value'      => $this->t('Test'),
            '#attributes' => [
              'class' => [
                'btn-primary',
                'btn-test-speech',
              ],
            ],
          ],
        ],
      ],
    ];

    return $form['general_settings'];
  }

  private function getEntitiesSettings(Config $config): array {
    /** @var \Drupal\node\Entity\NodeType[] */
    $contentTypes = $this->typeManager
      ->getStorage('node_type')
      ->loadMultiple();

    $options = [];
    foreach ($contentTypes as $type => $obj) {
      $name = $obj->get('name');
      $options[$type] = $name;
    }

    $form['entities_settings'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Content Types'),
      '#group'       => 'settings',
      '#description' => $this->t('<p><h2>Content Type Settings</h2></p>'),

      'content_types' => [
        '#type'          => 'checkboxes',
        '#title'         => $this->t('Content types to apply web speech field to.'),
        '#default_value' => $config->get('content_types') ?? [],
        '#required'      => TRUE,
        '#options'       => $options,
      ],
    ];

    return $form['entities_settings'];
  }

  /**
   * Obtiene el contenido del archivo CHANGELOG.md.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   * @param string $module_path
   *   Path del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getChangeLogBuild(Config $config,
                                     string $module_path): array {
    $template = file_get_contents($module_path . "/templates/custom/info.html.twig");

    $ruta = $module_path . "/CHANGELOG.md";
    $contenido = $this->getMdContent($ruta);

    if ($contenido) {
      $form['info'] = [
        '#type'        => 'details',
        '#title'       => $this->t('Info'),
        '#group'       => 'settings',
        '#description' => '',

        'info' => [
          '#type'     => 'inline_template',
          '#template' => $template,
          '#context'  => [
            'changelog' => Markup::create($contenido),
          ],
        ],
      ];

      return $form['info'];
    }

    return [];
  }

  /**
   * Obtiene el contenido del archivo LICENSE.md.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   * @param string $module_path
   *   Path del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getLicenseBuild(Config $config,
                                   string $module_path): array {
    $template = file_get_contents($module_path . "/templates/custom/license.html.twig");

    $ruta = $module_path . "/LICENSE.md";
    $contenido = $this->getMdContent($ruta);

    if ($contenido) {
      $form['license'] = [
        '#type'        => 'details',
        '#title'       => $this->t('License'),
        '#group'       => 'settings',
        '#description' => '',

        'license' => [
          '#type'     => 'inline_template',
          '#template' => $template,
          '#context'  => [
            'license' => Markup::create($contenido),
          ],
        ],
      ];

      return $form['license'];
    }

    return [];
  }

  /**
   * Obtiene el contenido del archivo LICENSE.md.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   * @param string $module_path
   *   Path del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getReadmeBuild(Config $config,
                                  string $module_path): array {
    $template = file_get_contents($module_path . "/templates/custom/help.html.twig");

    $ruta = $module_path . "/README.md";
    $contenido = $this->getMdContent($ruta);

    if ($contenido) {
      $form['help'] = [
        '#type'        => 'details',
        '#title'       => $this->t('Help'),
        '#group'       => 'settings',
        '#description' => '',

        'help' => [
          '#type'     => 'inline_template',
          '#template' => $template,
          '#context'  => [
            'readme' => Markup::create($contenido),
          ],
        ],
      ];

      return $form['help'];
    }

    return [];
  }

  /**
   * Obtiene el contenido de un archivo .md.
   *
   * @param string $path
   *   Ruta completa del archivo.
   *
   * @return string
   *   Contenido del archivo.
   */
  private function getMdContent(string $path): string {
    $parser = new MarkdownParser();

    $contenido = '';
    if (file_exists($path)) {
      $contenido = file_get_contents($path);
      $contenido = $parser->text($contenido);
    }

    return $contenido;
  }

}
