<?php

/**
 * @file
 * Contains \Drupal\test_d8\Form\TestDrupal8QcmForm
 */
namespace Drupal\test_d8\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;

class TestDrupal8QcmForm extends FormBase {

    protected $numberQuestions;
    protected $timeLeft;
    protected $percent;

    public function __construct(){
        $testD8Settings = $this->config('test_d8.settings');
        $this->numberQuestions = $testD8Settings->get('number_of_questions');
        $this->timeLeft = $testD8Settings->get('time_to_complete_test');
        $this->percent = $testD8Settings->get('percent');
    }

    public function getFormId(){
        return 'testd8_form';
    }

    public function getTitle(NodeInterface $node = null) {
        return $this->t('Test @name', array(
            '@name' => $node->getTitle(),
        ));
    }

    public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = null){
        $nid = $node->id();
        $uid = \Drupal::currentUser()->id();

        $cookie = isset($_COOKIE['testD8']) ? unserialize($_COOKIE['testD8']) : [];

        if (isset($cookie['nid']) && $cookie['nid'] == $nid){
            # get the 40 questions from cookie
            $questionsQcmList = $cookie['questions_list'];
            # add flash message
            #\Drupal::messenger()->addMessage('Vous n\'avez pas terminé un précédent test, commencé le '.format_date($cookie['date_start'], 'format_date_coding_game'), 'warning');

        } else {
            # get all questions id
            $questionIds = $this->getAllQuestionsId($node);
            # load all questions
            $questions = Paragraph::loadMultiple($questionIds);
            # get 40 random questions
            $questionsQcmList = $this->getCurrentQcmQuestions($questions);
            # storing q/a
            $cookieQuestionsData = $this->getCookieQuestionsData($questionsQcmList);

            $time = \Drupal::time()->getCurrentTime();
            # Set the cookie Test D8
            $storageData = [
                'nid' => $nid, # thème du test
                'questions_list' => $questionsQcmList, # liste des questions random
                'session_questions' => $cookieQuestionsData, # réponses données
                'date_start' => $time, # date de début du test
                'qcm_timer' => $this->timeLeft, # timer mis à jour toutes les X secondes
            ];
            setcookie('testD8', serialize($storageData), $time + 3600*24*365);
        }

        # nav mini-cercles
        $form['navisual'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['clearfix'],
                'id' => 'test_d8-navisual',
            ],
        ];
        $i = 0;
        foreach ($questionsQcmList as $data){
            $form['navisual']['circle'.$i] = [
                '#type' => 'html_tag',
                '#tag' => 'span',
                '#attributes' => [
                    'class' => ['test_d8-navisual-item'],
                    'data-qid' => $data['id'],
                    'data-pos' => $i,
                ],
                '#value' => ($i + 1),
            ];
            $i++;
        }

        # Q&A
        $i = 0;
        foreach ($questionsQcmList as $data){
            ++$i;

            $form['propositions'.$data['id']] = [
                '#type'     => 'radios',
                '#title'    => $this->t('Question @num', array('@num' => $i)),
                '#markup'   => '<div class="test_d8-question-text">'.$data['question'].'</div>',
                '#options'  => [
                    'p1' => $data['p1'],
                    'p2' => $data['p2'],
                    'p3' => $data['p3'],
                    'p4' => $data['p4'],
                ],
                '#prefix' => '<div class="test_d8-question'. ($i > 1 ? ' test_d8-hidden' : '') .'" id="test_d8-question'.$data['id'].'">',
                '#suffix' =>'</div>',
            ];
            # set previously answered question (saved in cookie)
            if (isset($cookie['session_questions'])){
                foreach ($cookie['session_questions'] as $value){
                    if (($value['id'] == $data['id']) && ($value['answer_num'] !== null)){
                        $form['propositions'.$data['id']]['#default_value'] = 'p'.$value['answer_num'];
                        break;
                    }
                }
            }
        }

        # nav
        $form['previous'] = [
            '#type' => 'button',
            '#value' => '◀',
            '#title' => $this->t('Previous question'),
            '#attributes' => ['title' => $this->t('Previous question')],
            '#id' => 'test_d8-question-prev',
            '#prefix' => '<div id="test_d8-nav">',
        ];
        $form['current_question'] = [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#prefix' => '<span id="test_d8-question-curr">',
            '#suffix' => '</span>',
            '#value' => null,
        ];
        $form['next'] = [
            '#type' => 'button',
            '#value' => '▶',
            '#title' => $this->t('Next question'),
            '#attributes' => ['title' => $this->t('Next question')],
            '#id' => 'test_d8-question-next',
            '#suffix' => '</div>',
        ];
        $form['validation'] = [
            '#type' => 'submit',
            '#value' => t('Valider le test'),
            '#attributes' => ['disabled' => 'disabled'],
            '#id' => 'test_d8-submit',
        ];

        return $form;
    }

    protected function getAllQuestionsId($node){
        $field_questions = $node->get('field_questions')->getValue();
        $ids = [];
        foreach ($field_questions as $d){
            $ids[] = $d['target_id'];
        }
        return $ids;
    }

    # get 40 random questions
    protected function getCurrentQcmQuestions($questions){
        $tmpList = [];
        foreach ($questions as $id => $para){
            $tmpList[] = [
                'id' => $id,
                'question' => $this->paragraphGetValue($para, 'field_question'),
                'p1' => $this->paragraphGetValue($para, 'field_proposition_1'),
                'p2' => $this->paragraphGetValue($para, 'field_proposition_2'),
                'p3' => $this->paragraphGetValue($para, 'field_proposition_3'),
                'p4' => $this->paragraphGetValue($para, 'field_proposition_4'),
                'reponse' => $this->paragraphGetValue($para, 'field_reponse')
            ];
        }
        shuffle($tmpList);
        $questionsList = array_slice($tmpList, 0, $this->numberQuestions);

        return $questionsList;
    }

    protected function paragraphGetValue($object, $fieldname){
        return  $object->get($fieldname)->getValue()[0]['value'];
    }

    protected function getCookieQuestionsData($questionsList){
        $cookieQuestions = [];
        foreach ($questionsList as $d){
            $cookieQuestions[] = [
                'id' => $d['id'],
                'answer_valid' => $d['reponse'],
                'answer_user' => null,
                'answer_num' => null,
            ];
        }
        return $cookieQuestions;
    }

    public function validateForm(array &$form, FormStateInterface $form_state){}

    public function submitForm(array &$form, FormStateInterface $form_state){
        $formData           = $form_state->getValues();
        $uid                = \Drupal::currentUser()->id();
        $node               = \Drupal::routeMatch()->getParameter('node');
        $nid                = $node->id();
        $certificationTitle = $node->getTitle();
        $cookie             = (isset($_COOKIE['testD8']) ? unserialize($_COOKIE['testD8']) : []);
        $cookieQuestions    = (isset($cookie['session_questions']) ? $cookie['session_questions'] : []);
        $scoreResult        = $this->getScoreResult($formData, $cookieQuestions);

        $this->setData([
            'uid' => $uid,
            'nid' => $nid,
            'certifTitle' => $certificationTitle,
            'scoreResult' => $scoreResult,
        ]);

        $this->getScoreMessage($scoreResult, $certificationTitle);
        $this->destroyCookie();
        $form_state->setRedirect('entity.user.canonical', ['user' => $uid]);
    }

    # Score calculation
    protected function getScoreResult($formData, $cookieQuestions){
        $score = 0;
        foreach ($formData as $field => $answer){
            if ('propositions' == substr($field, 0, 12)){
                $id = substr($field, 12);
                $answer = substr($answer, 1);
                foreach ($cookieQuestions as $d){
                    if ($d['id'] == $id){
                        if ($d['answer_num'] == $answer){
                            ++$score;
                        }
                        break;
                    }
                }
            }
        }
        return $score * 100 / $this->numberQuestions;
    }

    # Node creation
    protected function setData($arg){
        $titleScore = 'Test '.$arg['certifTitle'].' du '.format_date(\Drupal::time()->getCurrentTime(), 'format_date_coding_game');

        $node = Node::create(['type'=> 'score']);
        $node->set('title', $this->formatValueCT($titleScore));
        $node->set('uid', $this->formatValueCT($arg['uid'], 'target_id')) ;
        $node->set('field_score_nid', $this->formatValueCT($arg['nid'], 'target_id'));
        $node->set('field_score_result', $this->formatValueCT($arg['scoreResult']));
        $node->save();
    }

    # Format field value to create ContentType
    protected function formatValueCT($value, $key = 'value'){
        return array($key => $value);
    }

    protected function destroyCookie(){
        unset($_COOKIE['testD8']);
        setcookie('testD8', null, 0);
        //kint($_COOKIE);
        //exit;
    }

    # flash message
    protected function getScoreMessage($score, $certificationTitle){

        $messages = [
            'error' => $this->t('Test terminé.<br> Votre score est de <strong>@score&nbsp;%</strong><br>'.
                'Continuez à vous entrainer&nbsp;!', [
                    '@score' => $score,
                ]
            ),
            'warning' => $this->t('Test terminé.<br> Votre score est de <strong>@score&nbsp;%</strong><br>'.
                'Perséverez, vous y êtes presque&nbsp;!', [
                    '@score' => $score,
                ]
            ),
            'status' => $this->t('Test terminé.<br> Votre score est de <strong>@score&nbsp;%</strong><br>'.
                'Félicitations&nbsp;! En condition réelle, vous auriez obtenu la certification @certifTitle', [
                    '@score' => $score,
                    '@certifTitle' => $certificationTitle,
                ]
            ),
        ];

        if ($score < $this->percent['average']){
            $status = 'error';
        } elseif (($score >= $this->percent['average']) && ($score < $this->percent['graduation'])){
            $status = 'warning';
        } elseif ($score >= $this->percent['graduation']){
            $status = 'status';
        }

        \Drupal::messenger()->addMessage($messages[$status], $status);
    }
}
