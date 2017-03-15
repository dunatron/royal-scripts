<?php

class Page extends SiteTree
{

    private static $db = array();

    private static $has_one = array();
}

class Page_Controller extends ContentController
{

    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * array (
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * );
     * </code>
     *
     * @var array
     */
    private static $allowed_actions = array(
        'getEventFindaEvents',
        'getEventBriteEvents',
        'getAllEvents',
        'getEventFindaEvents',
        'EventFindaAPICall',
        'EventFindaUsers',
        'eventFindaBestImage'
    );

    public function EventFindaUserName()
    {
        return 'royalsocietynz';
    }

    public function EventFindaPassword()
    {
        return '2pr2nv6nb7n6';
    }

    public function init()
    {
        parent::init();
        // You can include any CSS or JS required by your project here.
        // See: http://doc.silverstripe.org/framework/en/reference/requirements
    }

    public function FindaUsers()
    {
        $users = array(
            'deathdouspartshow',
            'NZSO',
            'dnfringe'
        );
        return $users;
    }

    public function BriteUsers()
    {
        $users = array(
            'JPJWOZVYBY5QD6OOGL3W',
            'JPJWOZVYBY5QD6OOGL3W'
        );
        return $users;
    }

    public function FindaAPICall()
    {
        $findaEvents = array();
        $users = $this->FindaUsers();
        foreach ($users as $u){
            $events = $this->getEventFindaEvents($u);
            $findaEvents = array_merge($findaEvents, $events);
        }
        return $findaEvents;
    }

    public function FindaOffsetPages($collection)
    {
        $count = $collection->{'@attributes'}->count;
        $offset = $count / 20;
        $ceiling = ceil($offset);
        return $ceiling;
    }


    public function BriteAPICall()
    {
        $briteEvents = array();
        $tokens = $this->BriteUsers();
        foreach ($tokens as $t){
            $events = $this->getEventBriteEvents($t);
            $briteEvents = array_merge($briteEvents, $events);
        }
        return $briteEvents;
    }

    public function getEventFindaEvents($user)
    {
        // Request the response in JSON format using the .json extension
        // Request the response in JSON format using the .json extension
        $url = 'http://api.eventfinda.co.nz/v2/events.json?rows=20&username='.$user;

        $process = curl_init($url);
        curl_setopt($process, CURLOPT_USERPWD, $this->EventFindaUserName() . ":" . $this->EventFindaPassword());
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);

        $collection = json_decode($return);

        $offset = $this->FindaOffsetPages($collection);
        $EventArray = array();
        for ($i = 1; $i <= $offset; $i++) {
            if($i != 1){
                $url = 'http://api.eventfinda.co.nz/v2/events.json?rows=20&offset=20&username='.$user;
            } else {
                $count = $i-1;
                $addOffset = $count * 20;
                $url = 'http://api.eventfinda.co.nz/v2/events.json?rows=20&offset='.$addOffset.'&username='.$user;
            }

            $process = curl_init($url);
            curl_setopt($process, CURLOPT_USERPWD, $this->EventFindaUserName() . ":" . $this->EventFindaPassword());
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($process);

            $collection = json_decode($return);
            foreach ($collection->events as $event) {
                $e = new Event();
                $e->Title = $event->name;
                $e->ID = $event->id;
                $e->Description = $event->description;
                $e->StartDate = $event->datetime_start;
                $e->EndDate = $event->datetime_end;
                $e->Address = $event->address;
                $e->Venue = $event->location_summary;
                $e->IsLive = 'Live';
                $e->UserName = $event->username;
                if (isset($event->booking_phone)) {
                    $e->Phone = $event->booking_phone;
                }
                $e->EventURL = $event->url;
                if (isset($event->booking_web_site)) {
                    $e->TicketWebsite = $event->booking_web_site;
                }
                $e->Capacity = 'EventFinda:STILL TO FIND';
                // Handle Event Finda Images
                $images = $event->images->images;
                $bestImage = $this->eventFindaBestImage($images);
                $e->ExternalImageURL = $bestImage;
                $e->EventTopic = $event->category->name;

                array_push($EventArray, $e);
            }
        }

        return $EventArray;
    }

    public function eventFindaBestImage($images)
    {
        $imageURL = '';
        foreach ($images as $image) {
            $imageQuality = 0;
            $currQuality = 0;
            foreach ($image->transforms->transforms as $transform) {
                if ($transform->transformation_id == 7) {
                    $currQuality = 5;
                } elseif ($transform->transformation_id == 27) {
                    $currQuality = 4;
                } elseif ($transform->transformation_id == 8) {
                    $currQuality = 3;
                } elseif ($transform->transformation_id == 2) {
                    $currQuality = 2;
                } elseif ($transform->transformation_id == 15) {
                    $currQuality = 1;
                }
                if ($currQuality > $imageQuality) {
                    $imageQuality = $currQuality;
                    $imageURL = $transform->url;
                }
            }
        }
        return $imageURL;
    }

    /***
     * EventBrite (personal OAuth Token: JPJWOZVYBY5QD6OOGL3W)
     */
    public function getEventBriteEvents($token)
    {
        $briteService = new RestfulService('https://www.eventbriteapi.com/v3/users/me/owned_events/?token='.$token.'&expand=venue,logo,organizer,category');
        // perform the query
        $conn = $briteService->request();

        $collection = json_decode($conn->getBody());

        $EventArray = array();
        foreach ($collection->events as $event) {
            $e = new Event();
            if ($event->name->text) {
                $e->Title = $event->name->text;
            }
            if ($event->description->text) {
                $e->Description = $event->description->text;
            }
            if ($event->start->local) {
                $e->StartDate = $event->start->local;
            }
            if ($event->end->local) {
                $e->EndDate = $event->end->local;
            }
            if (isset($event->venue->address->localized_address_display)) {
                $e->Address = $event->venue->address->localized_address_display;
            }
            $e->Venue = 'Seems to be just ADDRESS';
            if ($event->status) {
                $e->IsLive = $event->status;
            }
            if ($event->organizer->name != NULL) {
                $e->UserName = $event->organizer->name;
            }
            $e->Phone = 'NO PHONE YET';
            if ($event->url) {
                $e->EventURL = $event->url;
            }
            $e->TicketWebsite = 'LOOK INTO FREE AND PURCHASABLE TICKETS';
            if ($event->capacity) {
                $e->Capacity = $event->capacity;
            }
            if ($event->logo != NULL) {
                $e->ExternalImageURL = $event->logo->original->url;
            }

            if (isset($event->category->name_localized)) {
                echo $event->category->name_localized;
                $e->EventTopic = $event->category->name_localized;
            }
            array_push($EventArray, $e);
        }

        return $EventArray;
    }

    public function getAllEvents()
    {
        $eventList = ArrayList::create();
        // Event Brite
        $briteCall = $this->BriteAPICall();
        $briteEvents = $briteCall;
        // Event Finda
        $findaCall = $this->FindaAPICall();
        $findaEvents = $findaCall;

        $eventList->merge($briteEvents);
        $eventList->merge($findaEvents);

        $data = ArrayData::create(array(
            'Events' => $eventList
        ));
        return $data;
    }

    public function ChimpService()
    {
        $chimpService = new RestfulService('https://us14.api.mailchimp.com/3.0/');
        return $chimpService;
    }

    public function getMailChimpLists()
    {
        $service = new RestfulService('https://us14.api.mailchimp.com/3.0/');
        $service->httpHeader('Authorization: apikey b055b193339e717f0e9aa5065b24949d-us14');
        $endpoint = 'lists';
        $response = $service->request($endpoint, 'GET');

        $body = $response->getBody();
        $jObject = json_decode($body);

        $ListArray = new ArrayList();
        foreach ($jObject->lists as $l){
            $listObject = new DataObject();
            $listObject->id=$l->id;
            $listObject->name=$l->name;
            $ListArray->add($listObject);
        }

        $data = ArrayData::create(array(
            'Lists' => $ListArray
        ));

        return $data;
    }

    public function createMailChimpCampaign()
    {
        $service = $this->ChimpService();
        $service->httpHeader('Authorization: apikey b055b193339e717f0e9aa5065b24949d-us14');
        $endpoint = 'campaigns';
        $obj = new stdClass();
        $obj->type = 'regular'; // type of campaign (regular,plaintext,absplit,rss,variate)
        $obj->recipients->list_id = 'dbf720e39a'; //the unique list id
        $obj->settings->subject_line = 'campaign subject';
        $obj->settings->title = 'Title of campaign';
        $obj->settings->from_name = 'Heath'; //The 'from' name of the campaign(not an email address)
        $obj->settings->reply_to = 'heath.dunlop@samdog.nz';//The reply to email address of the campaign
        $obj->settings->inline_css = TRUE;
        $obj->settings->template_id = 605;


        $jObject = json_encode($obj);
        $response = $service->request($endpoint, 'POST', $jObject);
        echo'<pre>';
        var_dump($response);
        echo'</pre>';
    }

    public function createMailChimpContent()
    {
        $css = '<style>h1{ color:blue;}</style>';
        $service = $this->ChimpService();
        $service->httpHeader('Authorization: apikey b055b193339e717f0e9aa5065b24949d-us14');
        $campaign_id = '677a7b8d4a';
        $endpoint = 'campaigns/'.$campaign_id.'/content';

        $obj = new stdClass();
        $obj->html = $css.'<h1>Hello content from my pc</h1><img src="http://www.imagesinspace.co.nz/site/imagesinspace/images/basic_theme/favicon.ico">';
        $data = json_encode($obj);
        $response = $service->request($endpoint, 'PUT', $data);
        var_dump($response);
    }

    public function getMailChimpTemplates()
    {
        $chimpService = new RestfulService('https://us4.api.mailchimp.com/3.0/');
        $service = $chimpService;
        $service->httpHeader('Authorization: apikey aec1aaaf3d113585538ca63cf101801c-us4');
        $endpoint = 'templates?count=50&folder_id=73649e8476';  //73649e8476->royal-2 folderid
        $response = $service->request($endpoint, 'GET');


        $body = $response->getBody();
        $jObject = json_decode($body);
        error_log($body);

        $TemplateArray = new ArrayList();
        foreach ($jObject->templates as $t){
            $obj = new DataObject();
            $obj->id=$t->id;
            $obj->name=$t->name;
            $obj->type=$t->type;
            $obj->thumbnail=$t->thumbnail;
            $TemplateArray->add($obj);
        }

        echo '<pre>';
        var_dump($jObject);
        echo '</pre>';

        $data = ArrayData::create(array(
            'Templates' => $TemplateArray
        ));
        return $data;
    }


    public function createMailChimpList()
    {

        $chimpService = new RestfulService('https://us14.api.mailchimp.com/3.0/');
        $chimpService->httpHeader('Authorization: apikey b055b193339e717f0e9aa5065b24949d-us14');
        //$chimpService->basicAuth('jeromer22', 'RealSamdog11!');

        $newList = '{
    "name": "Test",
    "contact": {
        "company": "royal Society from dev pc",
        "address1": "84 terrace, level 4",
        "address2": "",
        "city": "Wellington",
        "state": "wgtn",
        "zip": "6012",
        "country": "NZ",
        "phone": ""
    },
    "permission_reminder": "Royal society newsletter",
    "use_archive_bar": TRUE,
    "campaign_defaults": {
        "from_name": "Heath Dunlop",
        "from_email": "heath.dunlop@samdog.nz",
        "subject": "",
        "language": "en"
    },
    "notify_on_subscribe": "",
    "notify_on_unsubscribe": "",
    "email_type_option": TRUE,
    "visibility": "pub"
}';


        $data = json_encode($newList); //encode to json


        $endpoint = 'lists';

        $response = $chimpService->request($endpoint, 'POST', $data);

        $obj = new stdClass(); // Or DataObject
        $obj->name="PCTEST";
        $obj->contact->company = 'royal Society from dev pc';
        $obj->contact->address1 = '84 terrace, level 4';
        $obj->contact->address2 = '';
        $obj->contact->city = 'Wellington';
        $obj->contact->state = 'wgtn';
        $obj->contact->zip = '6012';
        $obj->contact->country = 'NZ';
        $obj->contact->phone = '';
        $obj->permission_reminder = 'Royal society newsletter';
        $obj->email_type_option = TRUE;
        $obj->campaign_defaults->from_name = 'Heath Dunlop';
        $obj->campaign_defaults->from_email = 'hetah.dunlop@samdog.nz';
        $obj->campaign_defaults->subject    = 'default';
        $obj->campaign_defaults->language   =   'en';

        $theJObject =  json_encode($obj);
        $response = $chimpService->request($endpoint, 'POST', $theJObject);

        echo'<pre>';
        var_dump($response);
        echo'</pre>';
        //return $stuff;
    }

}
