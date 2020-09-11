<?php

namespace Drupal\libcal;

use GuzzleHttp\Exception\RequestException;
use Masterminds\HTML5\Exception;
use Drupal\node\Entity\Node;

/**
 * Class EventDownloadService.
 */
class EventDownloadService implements EventDownloadServiceInterface
{

  /**
   * Constructs a new EventDownloadService object.
   */
  public function __construct()
  {

  }

  public function postAccessToken()
  {
    $config = \Drupal::config('libcal.libcalapiconfig');

    try {
      $response = \Drupal::httpClient()->post("https://libcal.library.utoronto.ca/1.1/oauth/token", [
        'json' => [
          'client_id' => $config->get("client_id"),
          'client_secret' => $config->get("client_secret"),
          'grant_type' => "client_credentials",
        ]
      ]);
      return json_decode((string)$response->getBody());
    } catch (RequestException $e) {
      print_r($e->getMessage());
      return null;
    }
  }

  public function get($params)
  {
    if (empty($params)) {
      throw new \Exception("keyword must be valid");
    }
    $access_token = $this->postAccessToken()->access_token;
    try {
      $curl = curl_init();
      $defaultOptions = [
        CURLOPT_URL => "https://libcal.library.utoronto.ca/1.1/$params",
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', "Authorization: Bearer " . $access_token),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
      ];
      //print_log($defaultOptions);
      curl_setopt_array($curl, $defaultOptions);
      $response = curl_exec($curl);
      //print_log($response);
      return json_decode($response);
    } catch (RequestException $e) {
      print_log($e->getMessage());
      return null;
    }
  }

  public function libcalEventToNode(array $events)
  {
    foreach ($events as $event) {
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', "event");
      $query->condition('field_libcal_id', $event->id);
      $nids = $query->execute();

      $startdate = explode("-", $event->start);
      array_pop($startdate);
      $startdate = implode("-", $startdate);

      $enddate = explode("-", $event->end);
      array_pop($enddate);
      $enddate = implode("-", $enddate);

      if (count($nids) <= 0) {
        // create new event node
        $params = [
          // The node entity bundle.
          'type' => 'event',
          'langcode' => 'en',
          'created' => time(),
          'changed' => time(),
          // The user ID.
          'uid' => 1,
          'moderation_state' => 'published',

          // libcal fields
          'title' => $event->title,
          'body' => [
            'summary' => substr(strip_tags($event->description), 0, 100),
            'value' => $event->description,
            'format' => 'full_html'
          ],
          'field_start_date' => $startdate,
          'field_end_date' => $enddate,
          'field_libcal_id' => $event->id, // need to make sure it's unique
          'field_featured_image' => $event->featured_image,
          'field_libcal_url' => $event->url->public,

          'field_all_day' => $event->allday,
          'field_calendar_id' => $event->calendar->id,

          'field_campus' => (!isset($event->campus) && is_object($event->campus) && isset($event->campus->name)) ? $event->campus->name : "",
          'field_geolocation' => !empty($event->geolocation) ? $event->geolocation : "",

          //'field_future_dates' => $event->future_dates,
          //'field_libcal_categories' => $event->category,
          'field_libcal_color' => $event->color,
          'field_location' => $event->location->name,
          'field_presenter' => $event->presenter,
          'field_registration' => $event->registration,
          'field_seats' => $event->seats,
          'field_seats_taken' => $event->seats_taken,
          'field_wait_list' => $event->wait_list
        ];
        //print_log($params);
        $node = Node::create($params);
        $node->save();
      } else {
        // update existing Event node
        $eventNode = Node::load(array_keys($nids)[0]);
        $eventNode->set('changed', time());
        // The user ID.
        $eventNode->set('title', $event->title);
        $eventNode->set('body', [
          'summary' => substr(strip_tags($event->description), 0, 100),
          'value' => $event->description,
          'format' => 'full_html'
        ]);
        $eventNode->set('field_start_date', $startdate);
        $eventNode->set('field_end_date', $enddate);
        $eventNode->set('field_libcal_id', $event->id); // need to make sure it's unique
        $eventNode->set('field_featured_image', $event->featured_image);
        $eventNode->set('field_libcal_url', $event->url->public);

        $eventNode->set('field_all_day', $event->allday);
        $eventNode->set('field_calendar_id', $event->calendar->id);
        $eventNode->set('field_campus', (!isset($event->campus) && is_object($event->campus) && isset($event->campus->name)) ? $event->campus->name : "");
        //$eventNode->set('field_future_dates', $event->future_dates);
        $eventNode->set('field_geolocation', $event->geolocation);
        //$eventNode->set('field_libcal_categories', $event->category);
        $eventNode->set('field_libcal_color', $event->color);
        $eventNode->set('field_location', $event->location->name);
        $eventNode->set('field_presenter', $event->presenter);
        $eventNode->set('field_registration', $event->registration);
        $eventNode->set('field_seats', $event->seats);
        $eventNode->set('field_seats_taken', $event->seats_taken);
        $eventNode->set('field_wait_list', $event->wait_list);
        $eventNode->save();
      }

    }

  }


}