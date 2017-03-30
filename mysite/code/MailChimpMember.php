<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30/03/17
 * Time: 2:10 PM
 */
class MailChimpMember extends Page
{

    private static $singular_name = "MailChimp Member";
    private static $plural_name = "MailChimp Members";
    private static $db = array();

    static $defaults = array(
        'ShowInMenus' => false,
        'ShowInSearch' => false
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

}

class MailChimpMember_Controller extends Page_Controller
{

    /**
     * array (
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if >checkAction() returns true
     * );
     * @var array
     */
    private static $allowed_actions = array();

    public function init()
    {
        parent::init();
        //$this->addMemberToMailChimpList('ca425d5957','simon.matthews@samdog.nz','Simon','Matthews');
    }

    /*
    * base MailChimp API call
    */
    public function ChimpService()
    {
        $chimpService = new RestfulService('https://us4.api.mailchimp.com/3.0/');
        $chimpService->httpHeader('Authorization: apikey aec1aaaf3d113585538ca63cf101801c-us4');
        return $chimpService;
    }

    /*
     * Get MailChimp Mailing Lists
     */
    public function getMailChimpLists()
    {
        $service = $this->ChimpService();
        $endpoint = 'lists';
        $response = $service->request($endpoint, 'GET');

        $body = $response->getBody();
        $jObject = json_decode($body);

        $DataArray = ArrayList::create();
        foreach ($jObject->lists as $l) {
            $listObject = new DataObject();
            $listObject->id = $l->id;
            $listObject->name = $l->name;
            $DataArray->add($listObject);
        }

        return $DataArray;
    }

    public function addMemberToMailChimpList($listID, $email, $fName, $lName)
    {
        //$listID = 'ca425d5957';
        $listName = $this->getListNameByID($listID);
//        $email = 'simon.matthews@samdog.nz';
        $obj = new stdClass();

        //$obj->list_id = $listID;
        $obj->email_address = $email;
        $obj->status = 'subscribed';
        $obj->merge_fields->FNAME = $fName;
        $obj->merge_fields->LNAME = $lName;

        $memberObject = json_encode($obj);

        $service = $this->ChimpService();
        $endpoint = 'lists/' . $listID . '/members';

        $response = $service->request($endpoint, 'POST', $memberObject);
        $responseBody = $response->getBody();

        $serverMailChimpMessage = json_decode($responseBody);

        if (isset($serverMailChimpMessage->title)) {
            if ($serverMailChimpMessage->title == 'Member Exists') {
                $subscriberHash = NULL;
                $membersArr = $this->getListMembers($listID);

                foreach ($membersArr as $member) {
                    if ($email == $member->email_address) {
                        $subscriberHash = $member->id;
                    }
                }
                if ($subscriberHash != NULL) {
                    $response = $this->putMemberInMailChimpList($subscriberHash, $listID, $email);
                } else {
                    return 'Subscriber Hash not found';
                }

                if ($response == 'pending') {
                    return 'Please check your email to be added to the mailing list for' . $email;
                } elseif ($response == 'subscribed') {
                    return $email . 'is already subscribed to this list';
                } elseif ($response == 405) {
                    return 'Something has gone terribly wrong';
                } else {
                    return $response;
                }
            }

        } elseif (isset($serverMailChimpMessage->status)) {
            if ($serverMailChimpMessage->status == 'subscribed') {
                var_dump($serverMailChimpMessage);
                return $email . ' Member has been successfully subscribed to ' . $listName;
            }
        }
        return 'Member added';
    }


    public function putMemberInMailChimpList($subscriberHash, $listID, $email)
    {
        $service = $this->ChimpService();
        $endpoint = 'lists/' . $listID . '/members/' . $subscriberHash;

        $putMember = new DataObject();

        $putMember->list_id = $listID;
        $putMember->status = 'subscribed';
        $putMember->email_address = $email;

        $memberPutObject = json_encode($putMember);

        $response = $service->request($endpoint, 'PATCH', $memberPutObject);
        $responseBody = $response->getBody();

        $body = json_decode($responseBody);

        return $body->status;
    }

    /*
     * members array containing (id,email_address)
     * the id is known as the subscriberHash in mailchimp
     */
    public function getListMembers($listID)
    {
        $service = $this->ChimpService();
        $endpoint = 'lists/' . $listID . '/members';
        $response = $service->request($endpoint, 'GET');
        $responseBody = $response->getBody();
        $body = json_decode($responseBody);

        $membersArr = ArrayList::create();
        foreach ($body->members as $member) {
            $obj = new DataObject();
            $obj->id = $member->id;
            $obj->email_address = $member->email_address;

            $membersArr->add($obj);
        }
        return $membersArr;
    }

    public function getListNameByID($listID)
    {
        $service = $this->ChimpService();
        $endpoint = 'lists/' . $listID;
        $response = $service->request($endpoint, 'GET');
        $body = json_decode($response->getBody());
        return $body->name;
    }
}


