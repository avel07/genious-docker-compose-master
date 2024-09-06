<?php

namespace Cube\Controllers;

use Bitrix\Main\Error;
use Cube\Controllers\BaseController;
/**
 * Контроллер Веб-форм
 */
class WebForm extends BaseController
{
    public const ALLOW_FIELD_TYPES = [
        'text',
        'textarea',
        'email',
        'hidden'
    ];

    /**
     * Получить поля для формы
     *
     * @param string $formId
     * @return array|null
     */
    public function getFormAction(string $formId): ?array
    {
        $result = [];
        $arForm = [];

        \Bitrix\Main\Loader::includeModule('form');
        $arForm["WEB_FORM_ID"] = \CForm::GetDataByID(
            $formId,
            $arForm["arForm"],
            $arForm["arQuestions"],
            $arForm["arAnswers"],
            $arForm["arDropDown"],
            $arForm["arMultiSelect"]
        );

        if (!$arForm["arForm"]) {
            $this->addError(new Error('Веб-форма не найдена'));
            return null;
        }

        // assign questions data
        foreach ($arForm["arQuestions"] as $arQuestion) {
            $formField = [];
            $fieldSid = $arQuestion["SID"];
            $formField = [
                "caption"       => $arForm["arQuestions"][$fieldSid]["TITLE_TYPE"] == "html" ? $arForm["arQuestions"][$fieldSid]["TITLE"] : nl2br(htmlspecialcharsbx($arForm["arQuestions"][$fieldSid]["TITLE"])),
                "isCaptionHtml" => $arForm["arQuestions"][$fieldSid]["TITLE_TYPE"] == "html",
                "required"      => $arForm["arQuestions"][$fieldSid]["REQUIRED"] == "Y",
            ];

            if (is_array($arForm["arAnswers"][$fieldSid])) {
                $fieldData = reset($arForm["arAnswers"][$fieldSid]);
                $formField['type']  = $fieldData['FIELD_TYPE'];
                $formField['field'] = implode('_', ['form', $formField['type'], $fieldData['ID']]);

                if (!in_array($fieldData['FIELD_TYPE'], static::ALLOW_FIELD_TYPES)) {
                    $this->addError(new Error('Тип поля "' . $fieldData['FIELD_TYPE'] . '" не поддерживается! Используйте стандартный компонент.'));
                    return null;
                }
            }

            $result['questions'][] = $formField;
        }

        $result['form'] = [
            'name'   => $arForm['arForm']['NAME'],
            'button' => $arForm['arForm']['BUTTON'],
            'desc'   => $arForm['arForm']['DESCRIPTION']
        ];

        return $result;
    }

    /**
     * Запись ответов формы
     *
     * @param string $formId
     * @param array $values
     * @return boolean
     */
    public function setResultAction(string $formId, array $values): bool
    {
        \Bitrix\Main\Loader::includeModule('form');
        $formData = $this->getFormAction($formId);
        if (!empty($this->getErrors()) || !$formData) {
            return false;
        }

        // Получаем обязательные поля
        $requiredFields = array_column(array_filter($formData, static function ($value) {
            return $value['required'] == true;
        }), 'field');

        // Проверяем заполненность полей
        $failRequiredValues = array_diff($requiredFields, array_keys($values));
        $failRequiredCaptions = [];
        if (!empty($failRequiredValues)) {
            foreach ($failRequiredValues as $field) {
                $formKey = array_search($field, array_column($formData, 'field'));
                $failRequiredCaptions[] = $formData[$formKey]['caption'];
            }

            $this->addError(new Error('Не заполнены следующие обязательные поля: ' . implode('; ', $failRequiredCaptions)));
            return false;
        }

        if ($resultId = \CFormResult::Add($formId, $values)) {
            \CFormResult::SetEvent($resultId);
            \CFormResult::Mail($resultId);
            return true;
        } else {
            global $strError;
            $this->addError(new Error($strError));
            return false;
        }
    }
}