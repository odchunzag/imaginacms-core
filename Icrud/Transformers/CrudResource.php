<?php

namespace Modules\Core\Icrud\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Iblog\Transformers\CategoryTransformer;

class CrudResource extends JsonResource
{
  /**
   * Method to merge values to response
   *
   * @return array
   */
  public function modelAttributes($request)
  {
    return [];
  }

  /**
   * Transform the resource into an array.
   * @param $request
   * @return array
   */
  public function toArray($request)
  {
    $response = []; //Default Response
    $translatableAttributes = $this->translatedAttributes ?? [];//Get translatable attributes
    $filter = json_decode($request->filter);//Get request Filters
    $languages = \LaravelLocalization::getSupportedLocales();// Get site languages

    //Add attributes
    foreach (array_keys($this->getAttributes()) as $fieldName) {
      $response[snakeToCamel($fieldName)] = $this->when(isset($this[$fieldName]), $this[$fieldName]);
    }

    //Add translatable attributes
    foreach ($translatableAttributes as $fieldName) {
      $response[snakeToCamel($fieldName)] = $this->when(isset($this[$fieldName]), $this[$fieldName]);
    }

    // Add translations
    if (isset($filter->allTranslations) && $filter->allTranslations) {
      foreach ($languages as $lang => $value) {
        foreach ($translatableAttributes as $fieldName) {
          $response[$lang][snakeToCamel($fieldName)] = $this->hasTranslation($lang) ? $this->translate($lang)[$fieldName] : '';
        }
      }
    }

    //Add media Files relation
    if (method_exists($this->resource, 'mediaFiles')) $response['mediaFiles'] = $this->mediaFiles();

    //Add model extra attributes
    $response = array_merge($response, $this->modelAttributes($request));

    //Response
    return $response;
  }
}