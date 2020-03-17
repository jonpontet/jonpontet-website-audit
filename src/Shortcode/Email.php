<?php
namespace JonPontet\WebsiteAudit\Shortcode;

use \JonPontet\WebsiteAudit\Core\Shortcode;
use \JonPontet\WebsiteAudit\WebsiteAuditPlugin;

class Email extends Shortcode {

  public function __construct() {
    parent::__construct('jpwa_email');
  }

  public function render($atts): string {
    $id = '';
    if (isset($atts['id'])) {
      $id = ' id ="' . $atts['id'].'"';
    }

    $error = '';
    if (isset($_GET['email_error'])) {
      $error = '
        <div class="alert alert-danger my-4" role="alert">
          '.esc_html($_GET['email_error']).'
        </div>
      ';
    }

    $success = '';
    if (isset($_GET['email_success'])) {
      $success = '
        <div class="alert alert-success my-4" role="alert">
          '.esc_html($_GET['email_success']).'
        </div>
      ';
    }

    return '
      <form class="jpwa-form jpwa-form--download" action="" method="POST" '.$id.'>
        <div class="form-group row">
          <div class="col-md-9">
              <input type="email" name="jpwa_email" class="form-control jpwa-form__email-input" required placeholder="'.__('Email', 'jpwa').'" aria-label="'.__('Email', 'jpwa').'" value="">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary jpwa-form__submit">'.__('Send', 'jpwa').'</button>
          </div>
        </div>
        <div class="form-check">
          <input name="jpwa_consent" type="checkbox" required class="form-check-input" id="jpwa-form__consent-input">
          <label class="form-check-label jpwa-form__consent-label" for="jpwa-form__consent-input">'.__('I consent to receive occasional emails', 'jpwa').'</label>
        </div>
        '.$success.'
      </form>
    ';
  }

}
