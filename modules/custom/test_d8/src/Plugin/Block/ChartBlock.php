<?php

namespace Drupal\test_d8\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a chart block.
 *
 * @Block(
 *   id = "chart_tests_drupal8",
 *   admin_label = @Translation("Dashboard Test Drupal 8 (Chart)"),
 * )
 */
class ChartBlock extends BlockBase {

  /**
  * {@inheritdoc}
  */
  public function build() {
    $themes = $this->getThemes();

    $config = \Drupal::config('test_d8.settings');
    $numberOfQuestions = $config->get('number_of_questions');

    $build['#theme'] = 'chart_tests_drupal8';

    $jsData = [];
    $hasAtLeastOneScore = false;
    $chartColors = $config->get('chart_colors');

    $i = 0;
    foreach ($themes as $themeId => $themeName){
      $scores = $this->getScoresByTheme($themeId);

      if (!empty($scores)){
        $hasAtLeastOneScore = true;
        // kint($themeName);
        // kint($scores);

        $dataPoints = [];
        foreach ($scores as $value){
          # date (* 1000 pour retourner des microsecondes, comme la méthode js Date.UTC(year, month, day))
          $date = $value['date_test'] * 1000;
          $dataPoints[] = [$date, (float)$value['score']];
        }

        $object = new \stdClass;
        $object->name = $themeName;
        $object->data = $dataPoints;
        $object->color = $chartColors[$i];
        ++$i;

        $jsData[] = $object;
      }
    }

    $build['#attached']['drupalSettings']['TestD8']['chart']['data'] = $jsData;
    $build['#data']['anytest'] = $hasAtLeastOneScore;

    # pas de cache pour ce block
    //$build['#cache']['max-age'] = 0;

    return $build;
  }

  protected function getThemes() {
    $node = \Drupal::entityTypeManager()->getStorage('node');
    $ids = \Drupal::entityQuery('node')->condition('type', 'test')->execute();
    $allThemes = $node->loadMultiple($ids);

    $themes = [];
    foreach($allThemes as $d){
      $themes[$d->id()] = $d->getTitle();
    }
    ksort($themes);
    return $themes;
  }

  # retourne tous les tests (filtrés par thème) de l'utilisateur courant
  protected function getScoresByTheme($themeId){
    $ids = \Drupal::entityQuery('node')
      ->condition('type','score')
      ->condition('uid', \Drupal::currentUser()->id())
      ->condition('field_score_nid', $themeId)
      ->sort('created', 'ASC')
      ->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $result = [];
    foreach ($nodes as $id => $obj){
      $result[] = [
        'score' => $obj->get('field_score_result')->getValue()[0]['value'],
        'date_test' => $obj->get('created')->getValue()[0]['value'],
      ];
    }
    return $result;
  }

}
