<?php
/**
 * @file
 * Contains \Drupal\imports_analytic_git\Plugin\migrate\source\SourceCompanies.
 */
namespace Drupal\imports_analytic_git\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateSource(
 *   id = "source_companies"
 * )
 */
class SourceCompanies extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Source data is queried from 'node_field_data' table.
    $query = $this->select('node_field_data', 'n');
	$query->join('node__field_id_company', 'i', 'i.entity_id = n.nid');
	$query->join('node__field_title_full', 't', 't.entity_id = n.nid');
	$query->join('node__field_id_google_analytics_profil', 'p', 'p.entity_id = n.nid');
    $query->fields('n', ['type', 'nid', 'title',]);
	$query->fields('i', ['field_id_company_value']);
    $query->fields('t', ['field_title_full_value']);
    $query->fields('p', ['field_id_google_analytics_profil_value']);
	//$query->fields('pm2', ['post_id']);
    $query->condition('n.type', 'company');
	//drush_print_r($query);
	//dpm($query);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'type'   => $this->t('Type'),
      'nid'   => $this->t('Node ID'),
      'title'   => $this->t('Title'),
	  'field_id_company_value' => $this->t('Company ID'),
      'field_title_full_value' => $this->t('Full title'),
      'field_id_google_analytics_profil_value' => $this->t('Profile Google Analytics ID'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    // Return a composed key
    return [
      'field_id_company_value' => [
        'type' => 'integer',
        'alias' => 'i',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // This example shows how source properties can be added in
    // prepareRow(). The source dates are stored as 2017-12-17
    // and times as 16:00. Drupal 8 saves date and time fields
    // in ISO8601 format 2017-01-15T16:00:00 on UTC.
    // We concatenate source date and time and add the seconds.
    // The same result could also be achieved using the 'concat'
    // and 'format_date' process plugins in the migration
    // definition.
    //$row->setSourceProperty('date', $date_now);
    // Set the title
    $title = $row->getSourceProperty('title');
	$row->setSourceProperty('title', $title);
    // Set the full title
    $title_full = $row->getSourceProperty('field_title_full_value');
	$row->setSourceProperty('title_full', $title_full);
    // Set company ID
    $id_company = $row->getSourceProperty('field_id_company_value');
	$row->setSourceProperty('id_company', $id_company);
    // Set the Google Analytics profile ID 
    $id_google_analytics_profile = $row->getSourceProperty('field_id_google_analytics_profil_value');
	$row->setSourceProperty('id_google_analytics_profile', $id_google_analytics_profile);
	//drush_print_r($row);
    return parent::prepareRow($row);
  }
}