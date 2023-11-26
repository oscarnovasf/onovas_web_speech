<?php

namespace Drupal\onovas_web_speech\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Bloque para Web Speech.
 *
 * @Block(
 *   id = "onovas_web_speech_block",
 *   admin_label = @Translation("Web Speech"),
 *   category = @Translation("onovas")
 * )
 */
class SpeechBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected RouteMatchInterface $routeMatch,
    protected ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /* Variable de retorno */
    $build = [];

    $config = $this->configFactory->get('onovas_web_speech.settings');
    $config_block = $this->getConfiguration();
    $content_types = $config->get('content_types');

    $route_name = $this->routeMatch->getRouteName();
    $isNode = strpos($route_name, 'entity.node.canonical') !== FALSE;
    $node_bundle = '';
    if (!$isNode) {
      return [];
    }

    $node_bundle = $this->routeMatch->getParameter('node')?->bundle();
    $isAllowed = in_array($node_bundle, $content_types);
    if ($isAllowed) {
      $build['node_onovas_web_speech'] = [
        '#theme'                  => 'onovas_web_speech_block',
        '#attached'               => [
          'library' => [
            'onovas_web_speech/speech',
          ],
          'drupalSettings' => [
            'onovas_web_speech' => [
              'voice'     => $config->get('voice'),
              'pitch'     => $config->get('pitch'),
              'rate'      => $config->get('rate'),
              'container' => $config_block['class'],
            ],
          ],
        ],
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->configFactory->get('onovas_web_speech.settings');
    $config_block = $this->getConfiguration();

    $form['class'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Select container class to read.'),
      '#default_value' => $config_block['class'] ?? $config->get('container'),
      '#required'      => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['class'] = $values['class'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
