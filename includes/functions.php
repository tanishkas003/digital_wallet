<?php
// includes/functions.php
session_start();

/** Escape output for HTML */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Flash messages */
function set_flash($msg, $type='info') {
    $_SESSION['flash'] = ['msg'=>$msg, 'type'=>$type];
}
function get_flash() {
    if(isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/** Login helpers */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** helper to bind params dynamically and execute (mysqli) */
function bind_and_execute($stmt, $types='', $params=array()) {
    if ($types !== '' && count($params) > 0) {
        // prepare references
        $bind_names = array();
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            // create variable variable
            $bind_name = 'param' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    return $res;
}
?>
