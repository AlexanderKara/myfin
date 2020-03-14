<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once 'consts.php';

class Transactions
{
    const DEBUG_MODE = true; // USE ONLY WHEN DEBUGGING THIS SPECIFIC CONTROLLER (this skips sessionkey validation)

    public static function getAllTransactionsForUser(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int) Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 3);
            } else {
                $mobile = false;
            }

            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            /* $db = new EnsoDB(true);

            $db->getDB()->beginTransaction(); */

            /* echo "1";
            die(); */
            $userID = UserModel::getUserIdByName($authusername, false);

            /* $accsArr = AccountModel::getWhere(
            ["users_user_id" => $userID],
            ["account_id", "name", "type", "description"]

            ); */

            $trxArr = TransactionModel::getAllTransactionsForUser($userID, false);

            /* $db->getDB()->commit(); */

            return sendResponse($response, EnsoShared::$REST_OK, $trxArr);
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function addTransaction(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            $amount = Input::validate($request->getParsedBody()['amount'], Input::$FLOAT, 2);
            $type = Input::validate($request->getParsedBody()['type'], Input::$STRICT_STRING, 3);
            $description = Input::validate($request->getParsedBody()['description'], Input::$STRING, 4);

            if (array_key_exists('entity', $request->getParsedBody())) {
                $entityID = Input::validate($request->getParsedBody()['entity_id'], Input::$INT, 5);
            } else {
                $entityID = null;
            }
            $accountFrom = Input::validate($request->getParsedBody()['account_from_id'], Input::$INT, 6);

            if (array_key_exists('account_to', $request->getParsedBody())) {
                $accountTo = Input::validate($request->getParsedBody()['account_to_id'], Input::$INT, 7);
            } else {
                $accountTo = null;
            }

            $categoryID = Input::validate($request->getParsedBody()['category_id'], Input::$INT, 8);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int) Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 9);
            } else {
                $mobile = false;
            }



            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            /* $db = new EnsoDB(true);

            $db->getDB()->beginTransaction(); */

            /* echo "1";
            die(); */
            //$userID = UserModel::getUserIdByName($authusername, false);

            /* $accsArr = AccountModel::getWhere(
            ["users_user_id" => $userID],
            ["account_id", "name", "type", "description"]

            ); */

            TransactionModel::insert([
                "date_timestamp" => time(),
                "amount" => $amount,
                "type" => $type,
                "description" => $description,
                "entities_entity_id" => $entityID,
                "accounts_account_from_id" => $accountFrom,
                "accounts_account_to_id" => $accountTo,
                "categories_category_id" => $categoryID
            ]);

            /* $db->getDB()->commit(); */

            return sendResponse($response, EnsoShared::$REST_OK, "Transaction added successfully!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }

    public static function removeTransaction(Request $request, Response $response, $args)
    {
        try {
            $key = Input::validate($request->getHeaderLine('sessionkey'), Input::$STRING, 0);
            $authusername = Input::validate($request->getHeaderLine('authusername'), Input::$STRING, 1);

            if ($request->getHeaderLine('mobile') != null) {
                $mobile = (int) Input::validate($request->getHeaderLine('mobile'), Input::$BOOLEAN, 2);
            } else {
                $mobile = false;
            }

            $trxID = Input::validate($request->getParsedBody()['transaction_id'], Input::$INT, 3);

            /* Auth - token validation */
            if (!self::DEBUG_MODE) {
                AuthenticationModel::checkIfsessionkeyIsValid($key, $authusername, true, $mobile);
            }

            /* Execute Operations */
            /* $db = new EnsoDB(true);

            $db->getDB()->beginTransaction(); */

            /* echo "1";
            die(); */
            $userID = UserModel::getUserIdByName($authusername, false);

            TransactionModel::delete([
                "transaction_id" => $trxID
            ]);

            /* $db->getDB()->commit(); */

            return sendResponse($response, EnsoShared::$REST_OK, "Transaction Removed!");
        } catch (BadInputValidationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_ACCEPTABLE, $e->getCode());
        } catch (AuthenticationException $e) {
            return sendResponse($response, EnsoShared::$REST_NOT_AUTHORIZED, $e->getCode());
        } catch (Exception $e) {
            return sendResponse($response, EnsoShared::$REST_INTERNAL_SERVER_ERROR, $e);
        }
    }
}

$app->get('/trxs/', 'Transactions::getAllTransactionsForUser');
$app->post('/trxs/', 'Transactions::addTransaction');
$app->delete('/trxs/', 'Transactions::removeTransaction');
$app->put('/trxs/', 'Accounts::editAccount');
