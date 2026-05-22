<?php
namespace Drupal\searcher_modal\Controller;

use Drupal\Core\Controller\ControllerBase;
class CustomQueryController {
     public function getInfoContent($id, $language) {
        $titleNode = "";
        $type = "";
        $connection = \Drupal::database();
        $query = $connection->select('node_field_data', 'n')->fields('n',array('nid', 'title', 'type'));
        $query->condition('n.langcode', $language)->condition("n.nid", $id)->condition("n.status", 1);
        $data = $query->execute();
        $results = $data->fetchAll(\PDO::FETCH_OBJ);
        $list = array();
        foreach ($results as $row) {
            $titleNode = $row->title;
            $type = $row->type;
        }
       return array(
         '#title' => $titleNode,
         '#type' => $type, 

        );
    }
    public function getSummaryContent ($id, $language) {
        $connection = \Drupal::database();
        $query = $connection->select('node__body', 'n')->fields('n',array('body_summary'));
        $query->condition('n.langcode', $language)->condition('n.entity_id', $id);
        $records = $query->execute()->fetchAll();
        $summary = "";
        foreach ($records as $record) {
            $summary = $record->body_summary;
        }
        return $summary;
    }
}