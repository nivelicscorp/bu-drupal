<?php

namespace Drupal\bu_lawyers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Html;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuLawyersController
 *
 * @author camilo.escobar
 */
class BuLawyersController extends ControllerBase {

  public function vCardGenerate(\Drupal\node\NodeInterface $node) {
    global $base_url;
    $language =  \Drupal::languageManager()->getCurrentLanguage()->getId();

    // $complete_name = $node->label();
    $full_name = $node->label();
    $photo = '';

    // Email
    if ($email = $node->field_email->getString()) {
      $email = Html::escape($email);
    }
    else {
      $email = '';
    }

    // Membership
    if ($membership_terms = $node->field_membership->referencedEntities()) {
      $membership_term = reset($membership_terms);
      $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($membership_term, $language);
      $title = Html::escape($translated_term->label());
    }
    else {
      $title = '';
    }

    // Address type
    $address_type = 'work';
    // Address
    $address = BU_COMPANY_ADDRESS;
    // Organization name
    $org = BU_COMPANY_NAME;

    // Not needed for now
    // City
//    $city = '';
//    $region = '';
//    if ($city_terms = $node->field_ciudad->referencedEntities()) {
//      $city_term = reset($city_terms);
//      $city = Html::escape($city_term->label());
//      // Region
//      if ($parent_terms = $entity_type_manager->getStorage('taxonomy_term')->loadParents($city_term->id())) {
//        $region_term = reset($parent_terms);
//        $region = Html::escape($region_term->label());
//      }
//    }
//
//    // Country
//    $country = BU_COMPANY_COUNTRY;

    // Telephone numbers
    $workphones = [];
    if ($phone_numbers = $node->field_telephone->getValue()) {
      foreach ($phone_numbers as $number) {
        $workphones[] = Html::escape($number['value']);
      }
    }

    // URL
    $url = $base_url;

    // Start building the vcard content
    $output = "N;CHARSET=UTF-8:" . $full_name . "\n";

    if ($org != "") {
      $output .= "ORG;CHARSET=utf-8:" . $org;
      $output .= "\n";
    }
    if ($full_name != "") {
      $output .= "FN;CHARSET=utf-8:" . $full_name;
      $output .= "\n";
    }
    if ($photo != "") {
      // Encode data to base64
      $photolink = file_get_contents($photo);
      $photodata = base64_encode($photolink);
      $output .= "PHOTO;ENCODING=BASE64;TYPE=JPEG:" . $photodata;
      $output .= "\n";
    }
    if ($email != "") {
      $output .= "EMAIL;TYPE=PREF,INTERNET:" . $email;
      $output .= "\n";
    }
    if ($title != "") {
      $output .= "TITLE;CHARSET=utf-8:" . $title;
      $output .= "\n";
    }
    $output .= "ADR;CHARSET=utf-8;TYPE=" . $address_type . ":;;" . $address;
    $output .= "\n";
    
    foreach ($workphones as $phone_number) {
      $output .= "TEL;TYPE=WORK,VOICE:" . $phone_number;
      $output .= "\n";
    }
    
    if ($url != "") {
      $output .= "URL:" . $url;
    }

    // Final VCard Output
    $begin_vcard = "BEGIN:VCARD\n";
    $end_vcard = "\nEND:VCARD";
    $final_output = $begin_vcard . $output . $end_vcard;

    // VCard file name
    $file_name = _bu_convert_string_to_meaningful_file_name($node->label()) . '.vcf';
    
    // Response object
    $response = new Response($final_output);

    $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file_name
    );

    $response->headers->set('Content-Disposition', $disposition);
    $response->headers->set('Content-Type', 'text/x-vcard; charset=utf-8');
    $response->headers->set('Content-Description', 'File Transfer');

    return $response;
  }

  public function resultadoTransaccion(){
    enviar_correo_bu();
    $respuestas = array(
      "",
      "Transacción aprobada",
      "Pago cancelado por el usuario",
      "Pago cancelado por el usuario durante validación",
      "Transacción rechazada por la entidad",
      "Transacción declinada por la entidad",
      "Fondos insuficientes",
      "Tarjeta invalida",
      "Acuda a su entidad",
      "Tarjeta vencida"
    );
    $_GET['message'] = $respuestas[$_GET['codigo_respuesta_pol']];
    $_GET["processingDate"] = date('d/m/Y H:i:s');
    if(isset($_GET['emailComprador'])) {
      return [
        '#theme' => 'resultado_transaccion',
        '#data' => $_GET,
      ];
    }else{
      $response = new Response("");
      return $response;
    }
  }

}
