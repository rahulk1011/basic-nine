<?php
/**
* @file
* Contains \Drupal\candidates\Controller\TestAPIController.
*/
namespace Drupal\candidates\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\user\Entity\Role;
use Drupal\node\Entity\Node;

//Controller routines for candidates routes

class CandidatesController extends ControllerBase {
	/**
	* Get All Candidates Details API
	*/
	public function get_candidate_detail(Request $request) {
		global $base_url;
		try{
			$content = $request->getContent();
			$params = json_decode($content, TRUE);
			$uid = $params['uid'];
			$user = User::load($uid);
			$all_candidates = array();

			$query_string = "SELECT node_field_data.langcode AS node_field_data_langcode, node_field_data.created AS node_field_data_created, node_field_data.nid AS nid
			FROM {node_field_data} node_field_data
			WHERE (node_field_data.status = '1') AND (node_field_data.type IN ('candidate'))
			ORDER BY node_field_data_created ASC";
			$candidate_fetch_details = \Drupal::database()->query($query_string)->fetchAll();

			foreach($candidate_fetch_details as $key => $node_id){
				$nodeid = $node_id->nid;
				$node = Node::load($nodeid);
				$candidate_details['candidate_id'] = $nodeid;
				$candidate_details['candidate_name'] = $node->get('title')->value;
				$date = date_create($node->get('field_birth_date')->value);
				$birth_date = date_format($date, "d/m/Y");
				$candidate_details['candidate_dob'] = $birth_date;
				$candidate_details['candidate_gender'] = $node->get('field_gender')->value;
				$candidate_details['candidate_mobile'] = $node->get('field_mobile')->value;
				$candidate_details['candidate_email'] = $node->get('field_email_id')->value;
				$candidate_details['candidate_city'] = $node->get('field_city')->value;
				$candidate_details['candidate_country'] = $node->get('field_country')->value;
				$candidate_details['candidate_description'] = $node->get('field_description')->value;
				array_push($all_candidates, $candidate_details);
			}
			$final_api_reponse = array(
				"status" => "OK",
				"message" => "All Candidate Details",
				"result" => $all_candidates
			);
			return new JsonResponse($final_api_reponse);
		}
		catch(Exception $exception) {
			$this->exception_error_msg($exception->getMessage());
		}
	}

	/**
	* Add Candidates Details API
	*/
	public function add_candidate_detail(Request $request){
		global $base_url;
		try{
			$content = $request->getContent();
			$params = json_decode($content, TRUE);

			$uid = $params['uid'];
			$user = User::load($uid);

			$date = explode('/', $params['candidate_dob']);
			$birth_date = $date[2] . "-" . $date[1] . "-" . $date[0];

			$newCandidate = Node::create([
				'type' => 'candidate',
				'uid' => 1,
				'title' => array('value' => $params['candidate_name']),
				'field_birth_date' => array('value' => $birth_date),
				'field_gender' => array('value' => $params['candidate_gender']),
				'field_mobile' => array('value' => $params['candidate_mobile']),
				'field_email_id' => array('value' => $params['candidate_email']),
				'field_city' => array('value' => $params['candidate_city']),
				'field_country' => array('value' => $params['candidate_country']),
				'field_description' => array('value' => $params['candidate_description']),
			]);

			// Makes sure this creates a new node
			$newCandidate->enforceIsNew();
			
			// Saves the node, can also be used without enforceIsNew() which will update the node if a $newCandidate->id() already exists
			$newCandidate->save();
			$nid = $newCandidate->id();
			$new_candidate_details = $this->fetch_candidate_detail($nid);
			$final_api_reponse = array(
				"status" => "OK",
				"message" => "Candidate Details Added Successfully",
				"result" => $new_candidate_details,
			);
			return new JsonResponse($final_api_reponse);
		}
		catch(Exception $exception) {
			$this->exception_error_msg($exception->getMessage());
		}
	}

	public function fetch_candidate_detail($nid){
		if(!empty($nid)){
			$node = Node::load($nid);

			$date = date_create($node->get('field_birth_date')->value);
			$birth_date = date_format($date, "d/m/Y");
			$candidate_details['candidate_name'] = $node->get('title')->value;
			$candidate_details['candidate_dob'] = $birth_date;
			$candidate_details['candidate_gender'] = $node->get('field_gender')->value;
			$candidate_details['candidate_mobile'] = $node->get('field_mobile')->value;
			$candidate_details['candidate_email'] = $node->get('field_email_id')->value;
			$candidate_details['candidate_city'] = $node->get('field_city')->value;
			$candidate_details['candidate_country'] = $node->get('field_country')->value;
			$candidate_details['candidate_description'] = $node->get('field_description')->value;

			$final_api_reponse = array(
				'candidate_detail' => $candidate_details
			);
			return $final_api_reponse;
		}
		else{
			$this->exception_error_msg("Candidate details not found.");
		}
	}

	/**
	* Edit Candidates Details API
	*/
	public function edit_candidate_detail(Request $request){
		global $base_url;
		try{
			$content = $request->getContent();/* reads json input from login API callback */
			$params = json_decode($content, TRUE);

			$uid = $params['uid'];
			$user = User::load($uid);

			$nid = $params['nid'];
			$date = explode('/', $params['candidate_dob']);
			$date_of_birth = $date[2] . "-" . $date[1] . "-" . $date[0];

			if(!empty($nid)){
				$node = Node::load($nid);
				$node->set("field_birth_date", array('value' => $date_of_birth));
				$node->set("field_gender", array('value' => $params['candidate_gender']));
				$node->set("field_mobile", array('value' => $params['candidate_mobile']));
				$node->set("field_email_id", array('value' => $params['candidate_email']));
				$node->set("field_city", array('value' => $params['candidate_city']));
				$node->set("field_country", array('value' => $params['candidate_country']));
				$node->set("field_description", array('value' => $params['candidate_description']));
				$node->save();
				$final_api_reponse = array(
					"status" => "OK",
					"message" => "Candidate Details Updated Successfully",
				);
			}
			else{
				$final_api_reponse = array(
					"status" => "FAIL",
					"message" => "Candidate ID is reqired",
				);
			}
			return new JsonResponse($final_api_reponse);
		}
		catch(Exception $exception) {
			$this->exception_error_msg($exception->getMessage());
		}
	}

	/**
	* Delete Candidates Details API
	*/
	public function delete_candidate(Request $request){
		global $base_url;
		try{
			$content = $request->getContent();
			$params = json_decode($content, TRUE);
			$nid = $params['nid'];
			if(!empty($nid)){
				$node = \Drupal::entityManager()->getStorage('node')->load($nid);
				$node->delete();
				$final_api_reponse = array(
					"status" => "OK",
					"message" => "Candidate record has been deleted successfully",
				);
			}
			return new JsonResponse($final_api_reponse);
		}
		catch(Exception $exception) {
			$web_service->error_exception_msg($exception->getMessage());
		}
	}
}