<?php

/**
 * @file
 * Contains \Drupal\candidates\Form\CandidatesForm.
*/

namespace Drupal\candidates\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class CandidatesForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'candidates_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$query = \Drupal::entityQuery('taxonomy_term');
		$query->condition('vid', "continent");
		$tids = $query->execute();
		$terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
		foreach ($terms as $term) {
			$continent_options[] = $term->name->value;
		}

		$form['candidate_name'] = array(
			'#type' => 'textfield',
			'#title' => t('Candidate Name'),
			'#required' => TRUE,
		);
		$form['candidate_dob'] = array(
			'#type' => 'date',
			'#title' => t('Birth Date'),
			'#required' => TRUE,
		);
		$form['candidate_gender'] = array(
			'#type' => 'select',
			'#title' => ('Gender'),
			'#required' => TRUE,
			'#options' => array(
				'male' => t('Male'),
				'female' => t('Female'),
				'other' => t('Other'),
			),
		);
		$form['candidate_mobile'] = array(
			'#type' => 'textfield',
			'#title' => t('Mobile'),
			'#required' => TRUE,
			'#maxlength' => 10,
		);
		$form['candidate_email'] = array(
			'#type' => 'email',
			'#title' => t('Email-ID'),
			'#required' => TRUE,
		);
		$form['candidate_city'] = array(
			'#type' => 'textfield',
			'#title' => t('City'),
			'#required' => TRUE,
		);
		$form['candidate_country'] = array(
			'#type' => 'textfield',
			'#title' => t('Country'),
			'#required' => TRUE,
		);
		$form['candidate_continent'] = array(
			'#type' => 'select',
			'#title' => t('Continent'),
			'#required' => TRUE,
			'#options' => $continent_options,
		);
		$form['candidate_description'] = array(
			'#type' => 'textarea',
			'#title' => t('Description'),
			'#required' => TRUE,
		);
		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Save Candidate Data'),
			'#button_type' => 'primary',
		);
		
		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if ($form_state->getValue('candidate_name') == '') {
			$form_state->setErrorByName('candidate_name', $this->t('Please Enter Name'));
		}
		if ($form_state->getValue('candidate_dob') == '') {
			$form_state->setErrorByName('candidate_dob', $this->t('Please Enter Birth Date'));
		}
		if ($form_state->getValue('candidate_gender') == '') {
			$form_state->setErrorByName('candidate_gender', $this->t('Please Select Gender'));
		}
		if ($form_state->getValue('candidate_mobile') == '') {
			$form_state->setErrorByName('candidate_mobile', $this->t('Please Enter Mobile'));
		}
		if ($form_state->getValue('candidate_email') == '') {
			$form_state->setErrorByName('candidate_email', $this->t('Please Enter Email-ID'));
		}
		if ($form_state->getValue('candidate_city') == '') {
			$form_state->setErrorByName('candidate_city', $this->t('Please Enter City'));
		}
		if ($form_state->getValue('candidate_country') == '') {
			$form_state->setErrorByName('candidate_country', $this->t('Please Enter Country'));
		}
		if ($form_state->getValue('candidate_continent') == '') {
			$form_state->setErrorByName('candidate_continent', $this->t('Please Select Continent'));
		}
		if ($form_state->getValue('candidate_description') == '') {
			$form_state->setErrorByName('candidate_description', $this->t('Please Enter Description'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$node = Node::create(['type' => 'candidate']);
		$node->langcode = "en";
		$node->uid = 1;
		$node->promote = 0;
		$node->sticky = 0;
		$node->title= $form_state->getValue('candidate_name');
		$node->field_birth_date = $form_state->getValue('candidate_dob');
		$node->field_gender = $form_state->getValue('candidate_gender');
		$node->field_mobile = $form_state->getValue('candidate_mobile');
		$node->field_email_id = $form_state->getValue('candidate_email');
		$node->field_city = $form_state->getValue('candidate_city');
		$node->field_country = $form_state->getValue('candidate_country');
		$node->field_continent->target_id = intval($form_state->getValue('candidate_continent')) + 1;
		$node->field_description = $form_state->getValue('candidate_description');
		$node->save();

		\Drupal::messenger()->addMessage(t("Candidate Data Save Successful"));
	}
}
