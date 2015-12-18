<?php namespace Mavenlink;

if (!function_exists('curl_init')) {
    throw new Exception('Mavenlink PHP API Client requires the CURL PHP extension');
}

require_once dirname(__FILE__) . '/Api/Object.php';
require_once dirname(__FILE__) . '/Api/Event.php';
require_once dirname(__FILE__) . '/Api/Expense.php';
require_once dirname(__FILE__) . '/Api/Invitation.php';
require_once dirname(__FILE__) . '/Api/Invoice.php';
require_once dirname(__FILE__) . '/Api/Participant.php';
require_once dirname(__FILE__) . '/Api/Post.php';
require_once dirname(__FILE__) . '/Api/Story.php';
require_once dirname(__FILE__) . '/Api/Assignment.php';
require_once dirname(__FILE__) . '/Api/TimeEntry.php';
require_once dirname(__FILE__) . '/Api/User.php';
require_once dirname(__FILE__) . '/Api/Workspace.php';

use Mavenlink\Api\Event;
use Mavenlink\Api\Expense;
use Mavenlink\Api\Invitation;
use Mavenlink\Api\Invoice;
use Mavenlink\Api\Participant;
use Mavenlink\Api\Post;
use Mavenlink\Api\Story;
use Mavenlink\Api\Assignment;
use Mavenlink\Api\TimeEntry;
use Mavenlink\Api\User;
use Mavenlink\Api\Workspace;
use Mavenlink\Api\Object;

class Api
{
    private static $devMode = true;
    private $loginInfo = null;
    private $models_namespace = 'Mavenlink\\Api\\';
	private $_retry_times = 3;
	private $_timeout_after = 10; // seconds
	private $_sleep_on_timeout = 25; // seconds

    function __construct($oauthToken, $production = true)
    {
        $this->loginInfo = $oauthToken;

        if ($production) {
            self::$devMode = false;
        }
    }

    function getWorkspaces($filters = array())
    {
        return $this->json2collection('Workspace', $this->getJsonForAll('Workspace', $filters));
    }

    function getEvents($filters = array())
    {
        return $this->json2collection('Event', $this->getJsonForAll('Event', $filters));
    }

    function getTimeEntries($filters = array())
    {
        return $this->json2collection('TimeEntry', $this->getJsonForAll('TimeEntry', $filters));
    }

    function getExpenses($filters = array())
    {
        return $this->json2collection('Expense', $this->getJsonForAll('Expense', $filters));
    }

    function getInvoices($filters = array())
    {
        return $this->json2collection('Invoice', $this->getJsonForAll('Invoice', $filters));
    }

    function getStories($filters = array())
    {
        return $this->json2collection('Story', $this->getJsonForAll('Story', $filters));
    }

    function getUsers($filters = array())
    {
        return $this->json2collection('User', $this->getJsonForAll('User', $filters));
    }

    function getTimeEntry($id)
    {
        return $this->getShowJsonFor('TimeEntry', $id);
    }

    function getExpense($id)
    {
        return $this->getShowJsonFor('Expense', $id);
    }

    function getInvoice($id)
    {
        return $this->getShowJsonFor('Invoice', $id);
    }
    function getAssignment($id) {
        return $this->getShowJsonFor('Assignment', $id);
    }
    function getAllAssignmentsFromWorkspace($workspaceId, $filters = array()) {
        return $this->json2collection('Assignment', $this->getJson(Assignment::getResourcesPath() . "?workspace_id=" . $workspaceId, $filters));
    }
    function getAllAssignmentsFromUser($assigneeId, $filters = array()) {
        return $this->json2collection('Assignment', $this->getJson(Assignment::getResourcesPath() . "?assignee_id=" . $assigneeId, $filters));
    }
    function getStory($id)
    {
        return $this->getShowJsonFor('Story', $id);
    }

    function getWorkspace($id)
    {
        return $this->getShowJsonFor('Workspace', $id);
    }

    function createWorkspace($workspaceParamsArray)
    {
        $workspaceParamsArray = $this->labelParamKeys('Workspace', $workspaceParamsArray);
        $newPath = Workspace::getResourcesPath();
        $curl = $this->createPostRequest($newPath, $this->loginInfo, $workspaceParamsArray);
        $response = $this->curlExec($curl);

        return $response;
    }

    function updateWorkspace($workspaceId, $workspaceParamsArray)
    {
        $workspaceParamsArray = $this->labelParamKeys('Workspace', $workspaceParamsArray);

        $updatePath = Workspace::getResourcePath($workspaceId);
        $curl = $this->createPutRequest($updatePath, $this->loginInfo, $workspaceParamsArray);
        $response = $this->curlExec($curl);

        return $response;
    }

    function inviteToWorkspace($workspaceId, $invitationParamsArray)
    {
        return $this->createNewForWorkspace('Invitation', $workspaceId, $invitationParamsArray);
    }

    function getAllParticipantsFromWorkspace($workspaceId, $filters = array())
    {
        return $this->json2collection('User', $this->getJson(User::getResourcesPath() . "?participant_in=" . $workspaceId, $filters));
    }

    function getAllInvoicesFromWorkspace($workspaceId, $filters = array())
    {
        return $this->json2collection('Invoice', $this->getJson(Invoice::getResourcesPath() . "?workspace_id=" . $workspaceId, $filters));
    }

    function getWorkspaceInvoice($workspaceId, $invoiceId)
    {
        return $this->getJson(Invoice::getWorkspaceResourcePath($workspaceId, $invoiceId));
    }

    function getAllPostsFromWorkspace($workspaceId, $filters = array())
    {
        return $this->json2collection('Post', $this->getJson(Post::getResourcesPath() . "?workspace_id=" . $workspaceId, $filters));
    }

    function createPostForWorkspace($workspaceId, $postParamsArray)
    {
        return $this->createNew(Post, $workspaceId, $postParamsArray);
    }

    function getWorkspacePost($workspaceId, $postId)
    {
        return $this->getJson(Post::getWorkspaceResourcePath($workspaceId, $postId));
    }

    function updateWorkspacePost($workspaceId, $postId, $updateParams)
    {
        return $this->updateModel('Post', $workspaceId, $postId, $updateParams);
    }

    function deleteWorkspacePost($workspaceId, $postId)
    {
        return $this->deleteModel('Post', $workspaceId, $postId);
    }

    function getAllStoriesFromWorkspace($workspaceId, $filters = array())
    {
        return $this->json2collection('Story', $this->getJson(Story::getResourcesPath() . "?workspace_id=" . $workspaceId, $filters));
    }

    function createStoryForWorkspace($workspaceId, $storyParamsArray)
    {
        return $this->createNew('Story', $workspaceId, $storyParamsArray);
    }

    function getWorkspaceStory($workspaceId, $storyId)
    {
        return $this->getJson(Story::getWorkspaceResourcePath($workspaceId, $storyId));
    }

    function updateWorkspaceStory($workspaceId, $storyId, $updateParams)
    {
        return $this->updateModel('Story', $workspaceId, $storyId, $updateParams);
    }

    function deleteWorkspaceStory($workspaceId, $storyId)
    {
        return $this->deleteModel('Story', $workspaceId, $storyId);
    }

    function getAllTimeEntriesFromWorkspace($workspaceId, $filters = array())
    {
        return $this->json2collection('TimeEntry', $this->getJson(TimeEntry::getResourcesPath() . "?workspace_id=" . $workspaceId, $filters));
    }

    function createTimeEntryForWorkspace($workspaceId, $timeEntryParamsArray)
    {
        return $this->createNew('TimeEntry', $workspaceId, $timeEntryParamsArray);
    }

    function getWorkspaceTimeEntry($workspaceId, $timeEntryId)
    {
        return $this->getJson(TimeEntry::getWorkspaceResourcePath($workspaceId, $timeEntryId));
    }

    function updateWorkspaceTimeEntry($workspaceId, $timeEntryId, $updateParams)
    {
        return $this->updateModel('TimeEntry', $workspaceId, $timeEntryId, $updateParams);
    }

    function deleteWorkspaceTimeEntry($workspaceId, $timeEntryId)
    {
        return $this->deleteModel('TimeEntry', $workspaceId, $timeEntryId);
    }

    function getAllExpensesFromWorkspace($workspaceId, $filters = array())
    {
        return $this->json2collection('Expense', $this->getJson(Expense::getResourcesPath() . "?workspace_id=" . $workspaceId, $filters));
    }

    function createExpenseForWorkspace($workspaceId, $expenseParamsArray)
    {
        return $this->createNew('Expense', $workspaceId, $expenseParamsArray);
    }

    function getWorkspaceExpense($workspaceId, $expenseId)
    {
        return $this->getJson(Expense::getWorkspaceResourcePath($workspaceId, $expenseId));
    }

    function updateWorkspaceExpense($workspaceId, $expenseId, $updateParams)
    {
        return $this->updateModel('Expense', $workspaceId, $expenseId, $updateParams);
    }

    function deleteWorkspaceExpense($workspaceId, $expenseId)
    {
        return $this->deleteModel('Expense', $workspaceId, $expenseId);
    }

	public function updateStory($storyId, $updateParams)
	{
		return $this->updateModelObject('Story', $storyId, $updateParams);
	}

    public function updateTimeEntry($timeEntryId, $updateParams)
    {
        return $this->updateModelObject('TimeEntry', $timeEntryId, $updateParams);
    }

    function getJsonForAll($model, $filters = array())
    {
        $model = $this->getFullClassname($model);
        $resourcesPath = $model::getResourcesPath();
        return $this->getJson($resourcesPath, $filters);
    }

    function getShowJsonFor($model, $id)
    {
        $model = $this->getFullClassname($model);
        $resourcePath = $model::getResourcePath($id);
        return $this->getJson($resourcePath);
    }

    function getJson($path, $filters = array())
    {
		$path = $this->applyFilters($path, $filters);
        $curl = $this->getCurlHandle($path, $this->loginInfo);

        $json = $this->curlExec($curl);

        return $json;
    }

    function createNew($model, $workspaceId, $params)
    {
        $model = $this->getFullClassname($model);

        $params = $this->labelParamKeys($model, array_merge($params, array('workspace_id' => $workspaceId)));

        $newPath = $model::getResourcesPath();
        $curl = $this->createPostRequest($newPath, $this->loginInfo, $params);
        $response = $this->curlExec($curl);

        return $response;
    }

    function createNewForWorkspace($model, $workspaceId, $params)
    {
      $model = $this->getFullClassname($model);

      $params = $this->labelParamKeys($model, $params);

      $newPath = $model::getWorkspaceResourcesPath($workspaceId);
      $curl = $this->createPostRequest($newPath, $this->loginInfo, $params);
      $response = $this->curlExec($curl);

      return $response;
    }

    function wrapParamFor($model, $arrayKey)
    {
        $model = $this->getClassname($model);

        return strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", "$model") . "[$arrayKey]");
    }

    function labelParamKeys($model, $paramsArray)
    {
        $model = $this->getFullClassname($model);
        $labelledArray = array();

        foreach ($paramsArray as $key => $value) {

            if ($this->keyAlreadyWrapped($model, $key)) {
                $wrappedKey = strtolower($key);
            } else {

                $wrappedKey = $this->wrapParamFor($model, $key);
            }

            $labelledArray[$wrappedKey] = $value;
        }

        return $labelledArray;
    }

    function keyAlreadyWrapped($object, $key)
    {
        $object = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', "_$1", "$object"));
        $matchPattern = "$object" . "\[\w+\]";
        $matchWrapped = 0;
        $matchWrapped = preg_match("/$matchPattern/", $key);

        return $matchWrapped == 1;
    }

    function updateModel($model, $workspaceId, $resourceId, $params)
    {
        $model = $this->getFullClassname($model);
        $updatePath = $model::getWorkspaceResourcePath($workspaceId, $resourceId);
        $curl = $this->createPutRequest($updatePath, $this->loginInfo, $params);

        $response = $this->curlExec($curl);

        return $response;
    }

	function updateModelObject($model, $resourceId, $params)
	{
		$model = $this->getFullClassname($model);
		$params = $this->labelParamKeys($this->getClassname($model), $params);
		$updatePath = $model::getResourcePath($resourceId);
		$curl = $this->createPutRequest($updatePath, $this->loginInfo, $params);

		$response = $this->curlExec($curl);

		return $response;
	}

    function deleteModel($model, $workspaceId, $resourceId)
    {
        $model = $this->getFullClassname($model);
        $resourcePath = $model::getWorkspaceResourcePath($workspaceId, $resourceId);
        $curl = $this->createDeleteRequest($resourcePath, $this->loginInfo);

        return $response = $this->curlExec($curl);
    }

	public function deleteStory($id)
	{
		return $this->deleteEntity('Story', $id);
	}

	public function deleteTimeEntry($id)
	{
		return $this->deleteEntity('TimeEntry', $id);
	}


	function deleteEntity($model, $resourceId)
	{
		$model = $this->getFullClassname($model);
		$resourcePath = $model::getResourcePath($resourceId);
		$curl = $this->createDeleteRequest($resourcePath, $this->loginInfo);

		return $response = $this->curlExec($curl);
	}

    function createPostRequest($url, $accessCredentials, $params)
    {
        $curlHandle = $this->getCurlHandle($url, $accessCredentials);
		$params = http_build_query($params);
		$params = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $params);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);

        return $curlHandle;
    }

    function createPutRequest($url, $accessCredentials, $params)
    {
        $curlHandle = $this->getCurlHandle($url, $accessCredentials);
		$params = http_build_query($params);
		$params = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $params);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);

        return $curlHandle;
    }

    function createDeleteRequest($url, $accessCredentials)
    {
        $curlHandle = $this->getCurlHandle($url, $accessCredentials);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $curlHandle;
    }

    public static function getBaseUri()
    {
        return self::$devMode ? 'https://mavenlink.local/api/v1/' : 'https://fips.mavenlink.com/api/v1/';
    }

    function getCurlHandle($url, $accessCredentials)
    {
        $curlOptions = array
        (
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $accessCredentials),
            CURLOPT_RETURNTRANSFER => TRUE
        );

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $curlOptions);

        if (self::$devMode) {
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        }

		curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $this->_timeout_after);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->_timeout_after);

        return $curlHandle;
    }

    public function json2collection($model, $json)
    {
        $model = $this->getFullClassname($model);
        $parsed_json = json_decode($json, true);
        $entities = $parsed_json[$model::$path];
        $collection = array();
        foreach($entities as $entity)
        {
            $object = new $model();
            foreach($entity as $field => $value)
            {
                $object->{$field} = $value;
            }
            $collection[] = $object;
        }
        return $collection;
    }

    private function getFullClassname($class)
    {
        return $this->models_namespace . $class;
    }

    private function getClassname($model)
    {
        $parts = explode('\\', $model);
        if (!count($parts))
        {
            return 0;
        }

        return array_pop($parts);
    }

	private function applyFilters($url, $filters= array())
	{
		if (!count($filters))
		{
			return $url;
		}

		$filters_str = $this->filtersToUrl($filters);
		$url .= strpos($url, '?') === false ? '?' : '&';
		return $url . $filters_str;
	}

	private function filtersToUrl($filters)
	{
		return http_build_query($filters);
	}


	private function curlExec($resource)
	{
		$try_number = 1;
		while(true)
		{
			if ($try_number > $this->_retry_times)
			{
				break;
			}

			$response = curl_exec($resource);
			if (curl_errno($resource) != 28) // not timeout error
			{
				break;
			}
			sleep($this->_sleep_on_timeout);
			$try_number++;
		}
		return $response;
	}

}

