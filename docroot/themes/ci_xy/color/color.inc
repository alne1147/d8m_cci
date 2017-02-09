<?php
$info = array(
  // Available colors and color labels used in theme.
  'fields' => array(
    'main-container' => t('main-container'),
    'navbar-collapse' => t('Navigation (Collapsible)'),
  ),

  // Pre-defined color schemes.
  'schemes' => array(
    'default' => array(
      'title' => t('Plain (default)'),
      'colors' => array(
        'main-container' => '#ffffff',
        'navbar-collapse' => '#f6f6f6',

      ),
    ),
    'firehouse' => array(
      'title' => t('Firehouse'),
      'colors' => array(
        'main-container' => '#cd2d2d',
        'navbar-collapse' => '#d64e4e',

      ),
    ),
    'ice' => array(
      'title' => t('Ice'),
      'colors' => array(
        'main-container' => '#d0d0d0',
        'navbar-collapse' => '#c2c4c5',

      ),
    ),
    'plum' => array(
      'title' => t('Plum'),
      'colors' => array(
        'main-container' => '#4c1c58',
        'navbar-collapse' => '#593662',
        'bg' => '#fffdf7',

      ),
    ),
    'slate' => array(
      'title' => t('Slate'),
      'colors' => array(
        'main-container' => '#4a4a4a',
        'navbar-collapse' => '#4e4e4e',
        'bg' => '#ffffff',

      ),
    ),
  ),

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => array(
    'css/colors.css',
  ),

  // Files to copy.
  'copy' => array(
    'logo.svg',
  ),
  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'bartik'),
      ],
    ],
  ],
);
