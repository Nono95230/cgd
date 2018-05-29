<?php
/
*
			// restriction aux dates passées uniquement
			if(isset($form['field_date_passage'])){
				
				// kint($form['field_date_passage']);

				$form['field_date_passage']['widget']['0']['value']['#default_value'] = null;
				$form['field_date_passage']['widget']['#after_build'] = array();


				$form['field_date_passage']['widget']['0']['value']['#default_value'] = date('Y-m-d');
				$form['field_date_passage']['widget']['0']['value']['#min'] = date('Y-m-d', mktime (0,0,0,date('m'),date('d'),date('Y')-40));
				$form['field_date_passage']['widget']['0']['value']['#max'] = date('Y-m-d');

				// This is a test, no decomment 
				//$form['field_date_passage']['widget']['0']['value']['#required'] = FALSE;

				// kint($form['field_date_passage']);
				// die();

				// $form['field_date_passage'] = array(
				// 	'#type'           => 'date',
				// 	'#title'          => t('Date de passage'),
				// 	'#min'            => '2000-01-01',
				// 	'#max'            => date('Y-m-d'),
				// 	'#default_value'  => date('Y-m-d'),
				// 	'#require'        => 'true'
				// );

			}
*/