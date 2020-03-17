<?php
namespace JonPontet\WebsiteAudit\Shortcode;

use \JonPontet\WebsiteAudit\Core\Shortcode;
use \JonPontet\WebsiteAudit\WebsiteAuditPlugin;

class Recent extends Shortcode {

  public function __construct() {
    parent::__construct('jpwa_recent');
  }

  public function render($atts): string {
    $result = WebsiteAuditPlugin::$result;

    if (!$result) {
      return '';
    }

    $html = '<div class="jpwa-recent-searches">';

    $categories = $result->getCategories();

    foreach ($categories as $k => $c) {
      $colorClass = (($c->getScore() >= 0.75) ? 'jpwa-result-score-color--great' : (($c->getScore() >= 0.5) ? 'jpwa-result-score-color--good' : 'jpwa-result-score-color--bad'));
      $bgColor = (($c->getScore() >= 0.75) ? 'rgba(76, 175, 80, .5)' : (($c->getScore() >= 0.5) ? 'rgba(255, 153, 0, .5)' : 'rgba(211, 47, 47, 0.5)'));
      $scoreLabel = (($c->getScore() >= 0.75) ? __('Great', 'jpwa') : (($c->getScore() >= 0.5) ? __('Good', 'jpwa') : __('Poor', 'jpwa')));

      $html .= '
        <div class="jpwa-result jpwa-result--' . $c->getId() . ' ' . $colorClass . '">
         <div class="jpwa-result__header">
            <h2 class="jpwa-result__title">' . $c->getTitle() . ' <sup class="jpwa-result__question fas fa-question-circle tooltip" title="' . $c->getSubtitle() . '"></sup></h2>
              <div class="jpwa-result-score">
                <div data-value="'. (int) ($c->getScore() * 100) . '" data-text="' . $c->getScoreFormatted() . '" data-color="' . $color . '" data-bg-color="' . $bgColor . '" class="jpwa-result-score__circle lugolabs-circle" id="circles-' . $c->getId() . '"></div>
                <div class="jpwa-result-score__label ' . $colorClass . '">' . __( $scoreLabel,'jpwa' ) . '</div>
              </div>
            </div>
          <p class="jpwa-result__description">' . $c->getDescription() . '</p>
        </div>
      ';
    }
    
    $html .= '</div>';

    return $html;
  }

}
