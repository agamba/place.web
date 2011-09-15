<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Run', 'main');

/**
 * BaseRun
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property Doctrine_Collection $Activity
 * @property Doctrine_Collection $ActivityType
 * @property Doctrine_Collection $Answer
 * @property Doctrine_Collection $AnswerConcept
 * @property Doctrine_Collection $Assessable
 * @property Doctrine_Collection $Assessment
 * @property Doctrine_Collection $Comment
 * @property Doctrine_Collection $Commentable
 * @property Doctrine_Collection $Concept
 * @property Doctrine_Collection $EloTemplate
 * @property Doctrine_Collection $Example
 * @property Doctrine_Collection $ExampleConcept
 * @property Doctrine_Collection $Question
 * @property Doctrine_Collection $QuestionConcept
 * @property Doctrine_Collection $ResolvedUserAlert
 * @property Doctrine_Collection $User
 * @property Doctrine_Collection $Votable
 * @property Doctrine_Collection $Vote
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseRun extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('run');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('name', 'string', 45, array(
             'type' => 'string',
             'length' => 45,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Activity', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('ActivityType', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Answer', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('AnswerConcept', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Assessable', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Assessment', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Comment', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Commentable', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Concept', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('EloTemplate', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Example', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('ExampleConcept', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Question', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('QuestionConcept', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('ResolvedUserAlert', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('User', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Votable', array(
             'local' => 'id',
             'foreign' => 'run_id'));

        $this->hasMany('Vote', array(
             'local' => 'id',
             'foreign' => 'run_id'));
    }
}