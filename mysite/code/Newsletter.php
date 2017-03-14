<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14/03/17
 * Time: 2:28 PM
 */
class Newsletter extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'MailingList' => 'Varchar(100)',
        'subject_line'=>'Varchar(100)',
        'from_name' =>  'Varchar(100)',
        'reply_to'  =>  'Varchar(100)',
        'MailChimpNewsletterID' =>  'Varchar(100)'
    );

    private static $has_one = array(
        'NewsLetterContent' =>  'NewsLetterContent'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $mailingList = DropdownField::create(
            'MailingList',
            'MailingList',
            $this->getMailChimpLists()
        );
        $subjectLine = TextField::create('subject_line', 'Subject Line')
            ->setDescription('Newsletter subject');
        $fromName = TextField::create('from_name', 'From Name')
            ->setDescription('The \'from\' name of the campaign(not an email address)');
        $replyTo = TextField::create('reply_to', 'Reply to')
            ->setDescription('The reply to email address of the campaign/newsletter');

        $fields->addFieldsToTab('Root.Main', array(
            $mailingList,
            $subjectLine,
            $fromName,
            $replyTo
        ));

        return $fields; // TODO: Change the autogenerated stub
    }


    public function onBeforeWrite()
    {
        parent::onBeforeWrite(); // TODO: Change the autogenerated stub
        error_log($this->MailingList);
        //error_log($this->MailChimpNewsletterID);
        $newsletterID = $this->createMailChimpCampaign();
        $this->MailChimpNewsletterID = $newsletterID;
    }

    public function ChimpService()
    {
        $chimpService = new RestfulService('https://us14.api.mailchimp.com/3.0/');
        return $chimpService;
    }

    /*
     * Get MailChimp Mailing Lists
     */
    public function getMailChimpLists()
    {
        $service = $this->ChimpService();
        $service->httpHeader('Authorization: apikey b055b193339e717f0e9aa5065b24949d-us14');
        $endpoint = 'lists';
        $response = $service->request($endpoint, 'GET');

        $body = $response->getBody();
        $jObject = json_decode($body);

        $ListArray = array();
        foreach ($jObject->lists as $l) {
            $listObject = new DataObject();
            $listObject->id = $l->id;
            $listObject->name = $l->name;
            $ListArray[$listObject->id] = $listObject->name;
        }

        return $ListArray;
    }

    public function createMailChimpCampaign()
    {
        $service = $this->ChimpService();
        $service->httpHeader('Authorization: apikey b055b193339e717f0e9aa5065b24949d-us14');
        $endpoint = 'campaigns';
        $obj = new stdClass();
        $obj->type = 'regular'; // type of campaign (regular,plaintext,absplit,rss,variate)
        $obj->recipients->list_id = $this->MailingList; //the unique list id
        $obj->settings->subject_line = $this->subject_line;
        $obj->settings->title = $this->Title;
        $obj->settings->from_name = $this->from_name; //The 'from' name of the campaign(not an email address)
        $obj->settings->reply_to = $this->reply_to;//The reply to email address of the campaign
        $obj->settings->inline_css = TRUE;


        $jObject = json_encode($obj);
        $response = $service->request($endpoint, 'POST', $jObject); // Newsletter is created at this point, we need response for its id to add content
        $body = $response->getBody();
        $jObject = json_decode($body);
        $newsLetterID = $jObject->id;
        return $newsLetterID;
    }
}