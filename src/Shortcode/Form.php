<?php
namespace JonPontet\WebsiteAudit\Shortcode;

use \JonPontet\WebsiteAudit\Core\Shortcode;
use \JonPontet\WebsiteAudit\WebsiteAuditPlugin;

class Form extends Shortcode {

  public function __construct() {
    parent::__construct('jpwa_form');
  }

  public function render($atts): string {
    $value = '';

    if (isset($atts['populate'])) {
      if (isset($_GET['jpwa_audit'])) {
        $value = $_GET['jpwa_audit'];
      } else if (WebsiteAuditPlugin::$result) {
        $value = WebsiteAuditPlugin::$result->getDomain();
      }
    }

    $title = '';
    if (isset($atts['title'])) {
      $title = '<p class="h2">'.$atts['title'].'</p>';
    }

    $id = '';
    if (isset($atts['id'])) {
      $id = ' id ="' . $atts['id'].'"';
    }

    $error = '';
    if (isset($_GET['audit_error'])) {
      $error = '
        <div class="alert alert-danger my-4" role="alert">
          '.esc_html($_GET['audit_error']).'
        </div>
      ';
    }

    return '
      <form class="jpwa-form jpwa-form--search" action="'.WebsiteAuditPlugin::getFormPageUrl().'" method="POST" '.$id.'>
        '.$title.'
        <div class="form-group row">
          <div class="col-md-9">
            <label class="sr-only" for="jpwa_audit">Website address</label>
            <input type="url" name="jpwa_audit" id="jpwa-domain" class="form-control jpwa-form__domain-input" required placeholder="'.__('Enter a website address', 'jpwa').'" aria-label="'.__('Enter a website address', 'jpwa').'" value="'.esc_attr($value).'">
            <small id="jpwa-domain-help" data-hidden="true" class="form-text text-muted">'.__('Your website address needs to look something like "https://jonpontet.com"', 'jpwa').'</small>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary jpwa-form__submit">'.__('Search', 'jpwa').'</button>
            <button class="d-none" type="submit" disabled>
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              '.__('Loading', 'jpwa').'
            </button>
          </div>
        </div>

        '.$error.'
      </form>
    ';
  }

}
