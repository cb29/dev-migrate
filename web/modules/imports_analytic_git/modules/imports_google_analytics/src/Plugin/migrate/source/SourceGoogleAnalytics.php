<?php
/**
 * @file
 * Contains \Drupal\imports_analytic_git\Plugin\migrate\source\SourceGoogleAnalytics.
 */
namespace Drupal\imports_google_analytics\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Google_Client;
use Google_Service_Analytics;

/**
 *
 * @MigrateSource(
 *   id = "source_google_analytics"
 * )
 */
class SourceGoogleAnalytics extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Source data is queried from 'node_field_data' table.
    $query = $this->select('node_field_data', 'n');
	$query->join('node__field_id_company', 'i', 'i.entity_id = n.nid');
    $query->join('node__field_id_google_analytics_profil', 'p', 'p.entity_id = n.nid');
    $query->fields('n', ['type', 'nid', 'title',]);
	$query->fields('i', ['field_id_company_value']);
	$query->fields('p', ['field_id_google_analytics_profil_value']);
    $query->condition('n.type', 'company');
	//drush_print_r($query);
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
      'field_id_google_analytics_profil_value' => $this->t('Google Analytics profile ID'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    // Define the yesterday date
    $date_yesterday = date('Ymd',strtotime('-1 days'));
    // Return a composed key
    return [
//      'date_yesterday' => [
//        'type' => 'integer',
//        'custom_setting' => $date_yesterday,
//      ],
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

    // Define the yesterday date
    $date_yesterday = date('Ymd',strtotime('-1 days'));
	  
    // Set the title
    $title = $date_yesterday .'-'. $row->getSourceProperty('field_id_company_value');
	$row->setSourceProperty('title', $title);
	  
    // Set company ID
	$row->setSourceProperty('company_id', $row->getSourceProperty('field_id_company_value'));
	  
    // Set the datas of Google Analytics
    if (class_exists('Google_Client') && class_exists('Google_Service_Analytics')) {
      // Create a new Google Client for authorization
      $client = new Google_Client();
      // Define the service account credentials file
      $credentials_file = 'service-account-credentials.json';
      // Build the authorization
      if (file_exists($credentials_file)) {
        // set the location manually
        $client->setAuthConfig($credentials_file);
      } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
        // use the application default credentials
        $client->useApplicationDefaultCredentials();
      } else {
        print "
		  
          <h3 class='warn'>
            Warning: You need download your Service Account Credentials JSON from the
            <a href='http://developers.google.com/console'>Google API console</a>.
          </h3>
          <p>
            Once downloaded, move them into the root directory of this repository and rename them 'service-account-credentials.json'.
          </p>
          <p>
            In your application, you should set the GOOGLE_APPLICATION_CREDENTIALS environment variable as the path to this file.
          </p>";
        return;
      }
      // Specify the application name
      $client->setApplicationName("Cobredia Analytic");
      // Specify the permissions
      $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
      // Create analytics from the application
      $service = new Google_Service_Analytics($client);
      // Specify the profile ID (View datas)
      $profile_id = $row->getSourceProperty('field_id_google_analytics_profil_value');
      // Generate the datas
      //$metrics_file = json_decode('metrics.json');
	  //$metrics_file = print('metrics.txt');
      // Specify the date of today for start and end dates
      $start_date = 'yesterday';
      $end_date = 'yesterday';
      $results = $service->data_ga->get('ga:' . $profile_id, $start_date, $end_date, 'ga:pageviews,ga:newUsers,ga:sessions,ga:sessionDuration,ga:percentNewSessions');
      // Encode results in json format
      //print json_encode($results);
	  $datas_google_analytics = json_encode($results);
      $row->setSourceProperty('datas_google_analytics', $datas_google_analytics);
	}
	
	// Prepare rows
	//drush_print_r($row);
    return parent::prepareRow($row);

  }
}