<?php

namespace Sprint\Migration;


class Version20230322075736 extends Version
{
    protected $description = "Веб-форма связи с менеджером";

    protected $moduleVersion = "4.2.4";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $formHelper = $helper->Form();
        $formId = $formHelper->saveForm(array (
  'NAME' => 'Консультация с менеджером',
  'SID' => 'SIMPLE_FORM_1',
  'BUTTON' => 'Отправить номер консультанту',
  'C_SORT' => '100',
  'FIRST_SITE_ID' => NULL,
  'IMAGE_ID' => NULL,
  'USE_CAPTCHA' => 'N',
  'DESCRIPTION' => 'Спасибо. Заявка принята. В ближайшее время с Вами свяжется наш менеджер.',
  'DESCRIPTION_TYPE' => 'text',
  'FORM_TEMPLATE' => '',
  'USE_DEFAULT_TEMPLATE' => 'Y',
  'SHOW_TEMPLATE' => NULL,
  'MAIL_EVENT_TYPE' => 'FORM_FILLING_SIMPLE_FORM_1',
  'SHOW_RESULT_TEMPLATE' => NULL,
  'PRINT_RESULT_TEMPLATE' => NULL,
  'EDIT_RESULT_TEMPLATE' => NULL,
  'FILTER_RESULT_TEMPLATE' => NULL,
  'TABLE_RESULT_TEMPLATE' => NULL,
  'USE_RESTRICTIONS' => 'N',
  'RESTRICT_USER' => '0',
  'RESTRICT_TIME' => '0',
  'RESTRICT_STATUS' => '',
  'STAT_EVENT1' => 'form',
  'STAT_EVENT2' => '',
  'STAT_EVENT3' => '',
  'LID' => NULL,
  'C_FIELDS' => '0',
  'QUESTIONS' => '1',
  'STATUSES' => '1',
  'arSITE' => 
  array (
    0 => 's1',
  ),
  'arMENU' => 
  array (
    'ru' => 'Консультация с менеджером',
    'en' => 'Консультация с менеджером',
  ),
  'arGROUP' => 
  array (
  ),
  'arMAIL_TEMPLATE' => 
  array (
    0 => 
    array (
      'EVENT_NAME' => 'FORM_FILLING_SIMPLE_FORM_1',
      'SUBJECT' => '#SERVER_NAME#: заполнена web-форма [#RS_FORM_ID#] #RS_FORM_NAME#',
    ),
  ),
));
        $formHelper->saveStatuses($formId, array (
  0 => 
  array (
    'CSS' => 'statusgreen',
    'C_SORT' => '100',
    'ACTIVE' => 'Y',
    'TITLE' => 'DEFAULT',
    'DESCRIPTION' => 'DEFAULT',
    'DEFAULT_VALUE' => 'Y',
    'HANDLER_OUT' => NULL,
    'HANDLER_IN' => NULL,
  ),
));
        $formHelper->saveFields($formId, array (
  0 => 
  array (
    'ACTIVE' => 'Y',
    'TITLE' => 'Телефон',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_316',
    'C_SORT' => '100',
    'ADDITIONAL' => 'N',
    'REQUIRED' => 'Y',
    'IN_FILTER' => 'Y',
    'IN_RESULTS_TABLE' => 'Y',
    'IN_EXCEL_TABLE' => 'Y',
    'FIELD_TYPE' => 'text',
    'IMAGE_ID' => NULL,
    'COMMENTS' => '',
    'FILTER_TITLE' => 'Телефон',
    'RESULTS_TABLE_TITLE' => 'Телефон',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => ' ',
        'VALUE' => '',
        'FIELD_TYPE' => 'text',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '0',
        'ACTIVE' => 'Y',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
));
    }

    public function down()
    {
        //your code ...
    }
}

