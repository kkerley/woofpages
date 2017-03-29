<?php
/**
 *  Copyright 2014 Taxamo, Ltd.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * $model.description$
 *
 * NOTE: This class is auto generated by the swagger code generator program. Do not edit the class manually.
 *
 */
class CreateRefundIn {

  static $swaggerTypes = array(
      'line_key' => 'string',
      'custom_id' => 'string',
      'amount' => 'number',
      'total_amount' => 'number'

    );

  /**
  * Line identifier. Either line key or custom id is required.
  */
  public $line_key; // string
  /**
  * Line custom identifier. Either line key or custom id is required.
  */
  public $custom_id; // string
  /**
  * Amount (without tax) to be refunded. Either amount or total amount is required.
  */
  public $amount; // number
  /**
  * Total amount, including tax, to be refunded. Either amount or total amount is required.
  */
  public $total_amount; // number
  }
