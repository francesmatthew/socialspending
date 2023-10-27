<?php
/*
    Request Types:
    - GET:  Can be used to get information about a group
        DEFAULT operation: get information about all groups for which a user is a member
            - Request:
                - Headers:
                    - cookies: session_id=***
                - URL Parameters:
                    - brief=[true | false]
            - Response:
                - Status Codes:
                    - 200 if group information was fetched correctly
                    - 400 if url parameters are invalid
                    - 401 if session_id cookie is not present or invalid
                    - 500 if the database could not be reached
                - Content-Type:application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>,
                        'groups':
                        [
                            {
                                'group_name':<GROUP NAME>,
                                'group_id:<GROUP ID>,
                                'debt':<DEBT>,
                                'members':
                                [
                                    {
                                        'username':<USERNAME>,
                                        'user_id:<USER ID>,
                                        'debt':<DEBT>
                                    },
                                    ...,
                                    {}
                                ]
                            },
                            ...,
                            {}
                        ]
                    }
                    The 'members' list does not include the currently logged in user.
                    'debt' is an integer value that is the (positive) amount the user owes or the (negative) amount the user is owed.
                    If `brief=true` in the URL parameters, the 'members' node is omitted from all groups.
                    <RESULT> is a message explaining the status code to a user.
        GROUP INFO operation: get information about a certain group
            - Request:
                - Headers:
                    - cookies: session_id=***
                - URL Parameters:
                    - brief=<true | false>
                    - groupID=<GROUP ID>
            - Response:
                - Status Codes:
                    - 200 if group information was fetched correctly
                    - 400 if url parameters are invalid
                    - 401 if session_id cookie is not present or invalid
                    - 404 if the currently logged in user is not a member of the specified group or the group does not exist
                    - 500 if the database could not be reached
                - Content-Type:application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>,
                        'group_name':<GROUP NAME>,
                        'group_id:<GROUP ID>,
                        'debt':<DEBT>,
                        'members':
                        [
                            {
                                'username':<USERNAME>,
                                'user_id:<USER ID>,
                                'debt':<DEBT>
                            },
                            ...,
                            {}
                        ]
                    }
                    The 'members' list does not include the currently logged in user.
                    'debt' is an integer value that is the (positive) amount the user owes or the (negative) amount the user is owed.
                    If `brief=true` in the URL parameters, the 'members' node is omitted.
                    <RESULT> is a message explaining the status code to a user.
    - POST: Used to perform multiple operations, where the operation is specified by a key provided in JSON
        CREATE operation: create a group and add the given users to the group
            - Request:
                - Headers:
                    - Content-Type: application/json
                    - cookies: session_id=***
                - body: serialized JSON in one of the following formats
                    {
                        'operation':'create',
                        'group_name':<GROUP NAME>,
                        'members':
                        [
                            {
                                'user':<USERNAME/EMAIL>,
                                'user_id:<USER ID>
                            },
                            ...,
                            {}
                        ]
                    }
                    Where either 'user':<USERNAME/EMAIL> OR 'user_id:<USER ID> are specified for each user.
                    If both username/email and user ID are specified, the user ID will be used.
                    'members' may be omitted or empty, in which case a group will be created with only the currently logged in user.
            - Response:
                - Status Codes:
                    - 200 if user successfully created the group and added all users
                    - 400 if request body is invalid
                    - 401 if session_id cookie is not present or invalid
                    - 404 if one or more specified user(s) does not exist
                    - 500 if the database could not be reached
                - Headers:
                    - Content-Type: application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>
                    }
                    Where <RESULT> is a message explaining the status code to a user
        ADD_USER operation: add a specified user to the specified group
            - Request:
                - Headers:
                    - Content-Type: application/json
                    - cookies: session_id=***
                - body: serialized JSON in one of the following formats
                    {
                        'operation':'add_user',
                        'group_id:<GROUP ID>,
                        'user':<USERNAME/EMAIL>,
                        'user_id:<USER ID>
                    }
                    And where either 'user':<USERNAME/EMAIL> OR 'user_id:<USER ID> are specified.
                    If both username/email and user ID are specified, the user ID will be used.
            - Response:
                - Status Codes:
                    - 200 if user was successfully added to the group
                    - 400 if request body is invalid
                    - 401 if session_id cookie is not present or invalid
                    - 404 if currently logged in user is not a member of this group, group does not exist, or user does not exist
                    - 500 if the database could not be reached
                - Headers:
                    - Content-Type: application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>
                    }
                    Where <RESULT> is a message explaining the status code to a user
        DELETE operation:   delete the specified group
                            only works if the currently logged in user is a member of the group
            - Request:
                - Headers:
                    - Content-Type: application/json
                    - cookies: session_id=***
                - body: serialized JSON in one of the following formats
                    {
                        'operation':'delete',
                        'group_id:<GROUP ID>,
                    }
            - Response:
                - Status Codes:
                    - 200 if group was successfully deleted
                    - 400 if request body is invalid
                    - 401 if session_id cookie is not present or invalid
                    - 404 if currently logged in user is not a member of this group or group does not exist
                    - 500 if the database could not be reached
                - Headers:
                    - Content-Type: application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>
                    }
                    Where <RESULT> is a message explaining the status code to a user
        RENAME operation:   change the name of the specified group
                            only works if the currently logged in user is a member of the group
            - Request:
                - Headers:
                    - Content-Type: application/json
                    - cookies: session_id=***
                - body: serialized JSON in one of the following formats
                    {
                        'operation':'rename',
                        'group_id:<GROUP ID>,
                        'group_new_name':<NEW GROUP NAME>
                    }
            - Response:
                - Status Codes:
                    - 200 if group was successfully renamed
                    - 400 if request body is invalid
                    - 401 if session_id cookie is not present or invalid
                    - 404 if currently logged in user is not a member of this group or group does not exist
                    - 500 if the database could not be reached
                - Headers:
                    - Content-Type: application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>
                    }
                    Where <RESULT> is a message explaining the status code to a user
        LEAVE operation:    remove the current user from the specified group, if they are present
            - Request:
                - Headers:
                    - Content-Type: application/json
                    - cookies: session_id=***
                - body: serialized JSON in one of the following formats
                    {
                        'operation':'leave',
                        'group_id:<GROUP ID>,
                    }
            - Response:
                - Status Codes:
                    - 200 if user successfully left the group
                    - 400 if request body is invalid
                    - 401 if session_id cookie is not present or invalid
                    - 404 if currently logged in user is not a member of this group or group does not exist
                    - 500 if the database could not be reached
                - Headers:
                    - Content-Type: application/json
                - body: serialized JSON in the following format
                    {
                        'message':<RESULT>
                    }
                    Where <RESULT> is a message explaining the status code to a user
*/

include_once('templates/connection.php');
include_once('templates/cookies.php');
include_once('templates/jsonMessage.php');

function handleGET($userID)
{
    global $mysqli;

    // Get URL parameters
    // 'brief' URL param
    $brief = false;
    if (isset($_GET['brief']) && $_GET['brief'] != 'false')
    {
        $brief = true;
    }
    // 'groupID' param
    if (isset($_GET['groupID']))
    {
        handleGetGroupInfo($userID, $brief);
    }

    // DEFAULT operation, get info about all groups of which the user is a member
    $groupArray = array();
    // make the request
    $sql =  'SELECT g.group_id, g.group_name FROM groups as g '.
            'INNER JOIN group_members as gm ON gm.group_id = g.group_id '.
            'WHERE gm.user_id = ?;';

    $result = $mysqli->execute_query($sql, [$userID]);
    // check that query was successful
    if (!$result)
    {
        // query failed, internal server error
        handleDBError();
    }

    // add each group to groupArray
    while ($row = $result->fetch_assoc())
    {
        $groupArray[] = $row;
    }

    for ($index = 0; $index < count($groupArray); $index++)
    {
        fillUserBalanceAndMembers($groupArray[$index], $userID, $brief);
    }

    // convert array to JSON and return it
    $returnArray = array();
    $returnArray['message'] = 'Success';
    $returnArray['groups'] = $groupArray;
    print(json_encode($returnArray));
    http_response_code(200);
    exit(0);
}

// function will add the current users' balance, and the members list (if brief==false)
// $group is the associative array for this group, and will be populated with data
// $userID is the user_id of the current user
// $brief indicates whether or not to include the 'members' list
function fillUserBalanceAndMembers(&$group, $userID, $brief)
{
    global $mysqli;

    $groupID = $group['group_id'];
    if ($brief)
    {
        // just get the current user's balance
        // query to get all debts to/from this user with other people in the group
        $sql =  'SELECT SUM(d.amount) as credits, 0 as debts FROM group_members as gm '.
                'INNER JOIN debts as d ON (d.debtor=gm.user_id AND d.creditor = ?) '.
                'WHERE gm.group_id = ? '.
                'UNION '.
                'SELECT 0 as credits, SUM(d.amount) as debts FROM group_members as gm '.
                'INNER JOIN debts as d ON (d.creditor=gm.user_id AND d.debtor = ?) '.
                'WHERE gm.group_id = ?;';
        $result = $mysqli->execute_query($sql, [$userID, $groupID, $userID, $groupID]);

        // check that query was successful
        if (!$result)
        {
            // query failed, internal server error
            handleDBError();
        }

        // calculate balance
        $debts = 0;
        while ($row = $result->fetch_assoc())
        {
            $debts = $debts + $row['debts'] - $row['credits'];
        }
        // store debt in group array
        $group['debt'] = $debts;
    }
    else
    {
        // get a list of all members and their balances
        // members will start as an associate array indexed by user_id
        $membersArray = array();
        // query to get all members
        $sql =  'SELECT u.user_id, u.username FROM group_members as gm '.
                'INNER JOIN users as u ON u.user_id=gm.user_id '.
                'WHERE gm.group_id = ?;';
        $result = $mysqli->execute_query($sql, [$groupID]);

        // check that query was successful
        if (!$result)
        {
            // query failed, internal server error
            handleDBError();
        }

        while ($row = $result->fetch_assoc())
        {
            $row['debt'] = 0;
            $membersArray[$row['user_id']] = $row;
        }

        // query to get all debts between members of the group
        $sql =  'SELECT d.creditor, d.debtor, d.amount FROM group_members as gm '.
                'INNER JOIN debts as d ON d.debtor=gm.user_id '.
                'WHERE gm.group_id = ? '.
                'INTERSECT '.
                'SELECT d.creditor, d.debtor, d.amount FROM group_members as gm '.
                'INNER JOIN debts as d ON d.creditor=gm.user_id '.
                'WHERE gm.group_id = ?;';
        $result = $mysqli->execute_query($sql, [$groupID, $groupID]);

        // check that query was successful
        if (!$result)
        {
            // query failed, internal server error
            handleDBError();
        }

        while ($row = $result->fetch_assoc())
        {
            $membersArray[$row['debtor']]['debt'] += $row['amount'];
            $membersArray[$row['creditor']]['debt'] -= $row['amount'];
        }

        // store balance for current user and remove current user from members
        $group['debt'] = $membersArray[$userID]['debt'];
        unset($membersArray[$userID]);

        // convert array to simple indexed array and store with group
        $group['members'] = array_values($membersArray);
    }
}

function handleGetGroupInfo($userID, $brief)
{
    global $mysqli;
    $groupID = $_GET['groupID'];

    // GROUP INFO operation, get info about a specific group, of which the user is a member
    // make the request
    $sql =  'SELECT g.group_id, g.group_name FROM group_members as gm '.
            'INNER JOIN groups as g ON g.group_id = gm.group_id '.
            'WHERE gm.user_id = ? AND gm.group_id = ?;';

    $result = $mysqli->execute_query($sql, [$userID, $groupID]);
    // check that query was successful
    if (!$result)
    {
        // query failed, internal server error
        handleDBError();
    }

    // check if the group exists
    if ($result->num_rows == 0)
    {
        returnMessage('Group does not exist or user is not a member', 404);
    }

    // get result with group_id and group_name
    $returnArray = $result->fetch_assoc();

    // populate user's balance and maybe members list
    fillUserBalanceAndMembers($returnArray, $userID, $brief);

    // convert result to JSON and return
    $returnArray['message'] = 'Success';
    print(json_encode($returnArray));
    http_response_code(200);
    exit(0);

}

function handlePOST($userID)
{
    // verify that json was set in header
    if ($_SERVER['CONTENT_TYPE'] != 'application/json')
    {
        returnMessage('Request header must have Content-Type: application/json', 400);
    }

    // parse JSON in body
    $body = file_get_contents('php://input');
    $bodyJSON = json_decode($body, true);
    if ($bodyJSON === null)
    {
        returnMessage('Request body has malformed json', 400);
    }

    // execute different functions based on operation
    if ($bodyJSON['operation'] === null)
    {
        returnMessage('Operation not specified in request', 400);
    }

    $operation = $bodyJSON['operation'];
    if ($operation == 'create')
    {
        handleCreate($userID, $bodyJSON);
    }
    elseif ($operation == 'add user')
    {
        handleAddUser($userID, $bodyJSON);
    }
    elseif ($operation == 'delete')
    {
        handleDelete($userID, $bodyJSON);
    }
    elseif ($operation == 'rename')
    {
        handleRename($userID, $bodyJSON);
    }
    elseif ($operation == 'leave')
    {
        handleLeave($userID, $bodyJSON);
    }
    returnMessage('Operation '.$operation.' not recognized', 400);

}

function handleCreate($userID, $bodyJSON)
{
    global $mysqli;

    // get new group name
    if ($bodyJSON['group_name'] === null || $bodyJSON['members'] === null)
    {
        returnMessage('group_name not set', 400);
    }
    $newGroupName = $bodyJSON['group_name'];

    // query to create group
    $sql = 'INSERT INTO groups (group_name) VALUES ( ? );';
    $result = $mysqli->execute_query($sql, [$newGroupName]);
    // check that query was successful
    if (!$result)
    {
        // query failed, internal server error
        handleDBError();
    }

    // get group ID
    $sql = 'SELECT LAST_INSERT_ID();';
    $result = $mysqli->execute_query($sql);
    // check that query was successful
    if (!$result)
    {
        // query failed, internal server error
        handleDBError();
    }
    // get groupID from result
    $groupID = $result->fetch_row()[0];


    // get a list of members to add to this group
    $newMembers = array();
    $newMembers[] = $userID;
    // array users that could not be found
    $usersInvalidJSON = array();
    $usersNotFound = array();
    if ($bodyJSON['members'] !== null)
    {
        foreach ($bodyJSON['members'] as $newMember)
        {
            // add user by user_id
            if ($newMember['user_id'] !== null)
            {
                // check that this user exists
                $userID = $newMember['user_id'];
                $sql = 'SELECT user_id from users WHERE user_id = ?;';
                $result = $mysqli->execute_query($sql, [$userID]);
                // check that query was successful
                if (!$result)
                {
                    // query failed, internal server error
                    handleDBError();
                }
                // check that user was found
                if ($result->num_rows == 0)
                {
                    $usersNotFound[] = $newMember;
                }
                // add user_id to list of member to add
                $row = $result->fetch_assoc();
                $newMembers[] = $row['user_id'];
            }
            // add user by username/email
            elseif ($newMember['user'] !== null)
            {
                // user username/email to get userID
                $user = $newMember['user'];
                $sql = 'SELECT user_id from users WHERE username = ? OR email = ?;';
                $result = $mysqli->execute_query($sql, [$user, $user]);
                // check that query was successful
                if (!$result)
                {
                    // query failed, internal server error
                    handleDBError();
                }
                // check that user was found
                if ($result->num_rows == 0)
                {
                    $usersNotFound[] = $newMember;
                }
                // add user_id to list of member to add
                $row = $result->fetch_assoc();
                $newMembers[] = $row['user_id'];
            }
            else
            {
                // neither 'user' nor 'user_id' was specified
                $usersInvalidJSON[] = $newMember;
            }
        }
    }


    // now add the members
    foreach ($newMembers as $userIDToAdd)
    {
        $sql = 'INSERT INTO group_members (group_id, user_id) VALUES (?, ?);';
        $result = $mysqli->execute_query($sql, [$groupID, $userIDToAdd]);
        // check that query was successful
        if (!$result)
        {
            // query failed, internal server error
            handleDBError();
        }
    }

    // print users that with malformed JSON
    $numUsersInvalid = count($usersInvalidJSON);
    if ($numUsersInvalid > 0)
    {
        $usersInvalidString = 'Missing \'user\' or \'user_id\' key for users: ';
        for ($index = 0; $index < $numUsersInvalid; $index++)
        {
            // append this use JSON that could not be found
            $usersInvalidString = $usersInvalidString.json_encode($usersInvalidJSON[$index]);
            // if not the last entry, also append a comma
            if ($index != $numUsersInvalid - 1)
            {
                $usersInvalidString = $usersInvalidString.', ';
            }
        }
        returnMessage($usersInvalidString, 400);
    }

    // print users that could not be found
    $numUsersNotFound = count($usersNotFound);
    if ($numUsersNotFound > 0)
    {
        $usersNotFoundString = 'Could Not find the following users: ';
        for ($index = 0; $index < $numUsersNotFound; $index++)
        {
            // append this use JSON that could not be found
            $usersNotFoundString = $usersNotFoundString.json_encode($usersNotFound[$index]);
            // if not the last entry, also append a comma
            if ($index != $numUsersNotFound - 1)
            {
                $usersNotFoundString = $usersNotFoundString.', ';
            }
        }
        returnMessage($usersNotFoundString, 404);
    }

    // otherwise, success
    returnMessage('Success', 200);
}

function handleAddUser($userID, $bodyJSON)
{
    global $mysqli;

}

function handleDelete($userID, $bodyJSON)
{
    global $mysqli;

}

function handleRename($userID, $bodyJSON)
{
    global $mysqli;

}

function handleLeave($userID, $bodyJSON)
{
    global $mysqli;

}

// user must have valid sessionID
$userID = validateSessionID();
if ($userID == 0)
{
    // failed to validate cookie, check if it was db error or just invalid cookie
    if ($_VALIDATE_COOKIE_ERRORNO == SESSION_ID_INVALID)
    {
        returnMessage('Invalid session_id cookie', 401);
    }
    handleDBError();
}

// handle different request types
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    handleGET($userID);
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    handlePOST($userID);
}
returnMessage('Request method not supported', 400);
?>
