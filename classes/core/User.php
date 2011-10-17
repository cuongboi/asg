<?php

include_once("DbTable.php");

class User extends DbTable {
    protected $_table;

    function __construct($id){
        global $config;
        $this->_table = $config['tables']['users_table'];
        parent::__construct($id);
    }

    function getId(){
        return $this->get('id');
    }

    function getUsername(){
        return $this->get('username');
    }

    function getPassword(){
        return $this->get('password');
    }

    function getFullname(){
        return $this->get('full_name');
    }

    function getAccess(){
        return $this->get('access_level');
    }

    function getCssId(){
        return $this->get('css_id');
    }

    function getEmail(){
        return $this->get('email');
    }

    function getBalance(){
        return $this->get('account_balance');
    }

    function getLastActive(){
        return $this->get('last_active');
    }

    function getTimezone(){
        return $this->get('timezone');
    }

    function setId($new_id){
        $this->set('id',$new_id);
    }

    function setUsername($new_username){
        $this->set('username',$new_username);
    }

    function setPassword($new_password){
        $password = crypt($new_password);
        $this->set('password',$password);
    }

    function setFullname($new_fullname){
        $this->set('full_name',$new_fullname);
    }

    function setAccess($new_access){
        $this->set('access_level',$new_access);
    }

    function setCssId($new_css){
        $this->set('css_id',$new_css);
    }

    function setEmail($new_email){
        $this->set('email',$new_email);
    }

    function setBalance($new_balance){
        $this->set('balance',$new_balance);
    }

    function setLastActive($new_la){
        $this->set('last_active',$new_la);
    }

    function setTimezone($new_tz){
        $this->set('timezone',$new_tz);
    }

    function registerLogin(){
        global $uniqueID;
        $_SESSION['session_userid'] = $this->get('id');
        $_SESSION['session_username'] = $this->get('username');
        $_SESSION['session_fullName'] = $this->get('full_name');
        $_SESSION['session_accessLevel'] = $this->get('access_level');
        $_SESSION['session_balance'] = $this->get('balance');
        $_SESSION['session_timezone'] = $this->get('timezone');
        $_SESSION['css_id'] = $this->get('css_id');
        $_SESSION['session_loggedIn'] = $uniqueID;
        $prefs = $this->getAllPreferences(); 
        foreach($prefs as $pref_name => $pref_value){
            $_SESSION[$pref_name] = $pref_value;
        }
    }

    function loadAspectSet($set_id){
        $aspset = new AspectSet($set_id);
        $prefs = $aspset->getPreferences();

        foreach($prefs as $name => $value){
            //print "<p>$name - $value</p>";
            $this->setAspectPreference($name, $value);
            $_SESSION[$name] = $value;
        }
    }

    function getCssRules(){
        $dbh = $this->_dbh;
        $id = $this->_id;
        global $config;
        $css_table = $config['tables']['css_rules_table'];
        $q = "SELECT id FROM $css_table WHERE uid = ?";
        $sth = $dbh->prepare($q);
        $sth->execute(array($id));
        while($row = $sth->fetch()){
            $returnMe[] = new CssRule($row['id']);
        }
        return $returnMe;
        
    }

    function offPreference($pref){
        $this->setAspectPreference($pref,0);
        $_SESSION[$pref] = 0;
    }

    function onPreference($pref){
        $this->setAspectPreference($pref,1);
        $_SESSION[$pref] = 1;
    }

    function togglePreference($pref){
     
        $pref_value = $_SESSION[$pref];

        if($pref_value == 0){
            $this->setAspectPreference($pref, 1);
            $_SESSION[$pref]=1;
        }else{
            $this->setAspectPreference($pref, 0);
            $_SESSION[$pref]=0;
        }

    }

    function logout(){
        $_SESSION['session_loggedIn']="xxx";
        session_destroy();
    }

    function updateLastActive(){
        $table = $this->_table;
        $id = $this->_id;
        $dbh = $this->_dbh;

        $sql = "UPDATE $table SET last_active=NOW() WHERE id=?";
        $sth = $dbh->prepare($sql);
        $sth->execute(array($id));
    }

    function setAspectPreference($prefname, $value){
        global $config;
        $aprefs = $config['tables']['aspect_preferences_table'];
        $uid = $this->_id;
        $dbh = $this->_dbh;

        $q = "UPDATE $aprefs SET $prefname=? WHERE user_id=?";
        $r = $dbh->prepare($q);
        $r->execute(array($value,$uid));
        $_SESSION[$prefname] = $value;
    }

    function setUserPreference($prefname, $value){
        $uid = $this->_id;
        $dbh = $this->_dbh;
        global $config;
        $uprefs = $config['tables']['user_preferences_table'];

        $q = "UPDATE $uprefs SET $prefname=? WHERE user_id=?";
        $r = $dbh->prepare($q);
        $r->execute(array($value,$uid));
        
        $_SESSION[$prefname] = $value;
    }
 
    function getAspectPreference($pref_name){
        $uid = $this->_id;
        $dbh = $this->_dbh;
        global $config;
        $aprefs = $config['tables']['aspect_preferences_table'];

        $q = "SELECT $pref_name FROM $aprefs WHERE user_id=?";
        $sth = $dbh->prepare($q);
        $sth->execute(array($uid));
        $row = $sth->fetch();
        return $row[$pref_name];
    }

    function getUserPreference($pref_name){
        $uid = $this->_id;
        $dbh = $this->_dbh;
        global $config;
        $uprefs = $config['tables']['user_preferences_table'];

        $q = "SELECT $pref_name FROM $uprefs WHERE user_id=?";
        $sth = $dbh->prepare($q);
        $sth->execute(array($uid));
        $row = $sth->fetch();
        return $row[$pref_name];
    }

    function getAllPreferences(){
        $uid = $this->_id;
        $dbh = $this->_dbh;
        global $config;
        $dbname = $config['db']['dbname'];
        $uprefs = $config['tables']['user_preferences_table'];
        $aprefs = $config['tables']['aspect_preferences_table'];

        $sql = "SELECT * FROM $uprefs WHERE user_id=?";
        $sth = $dbh->prepare($sql);
        $sth->execute(array($uid));
        $row = $sth->fetch();

        $colsql = "select column_name from information_schema.columns where table_name='$uprefs' AND table_schema='$dbname'";
        $colres = $dbh->query($colsql);

        while($colrow = $colres->fetch()){
            $colname = $colrow['column_name'];
            if($colname != 'id' && $colname != 'user_id'){
                $returnMe[$colname] = $row[$colname];
            }
       }

        $sql = "SELECT * FROM $aprefs WHERE user_id=?";
        $sth = $dbh->prepare($sql);
        $sth->execute(array($uid));
        $row = $sth->fetch();

        $colsql = "select column_name from information_schema.columns where table_name='$aprefs' AND table_schema='$dbname'";
        $colres = $dbh->query($colsql);

        while($colrow = $colres->fetch()){
            $colname = $colrow['column_name'];
            if($colname != 'id' && $colname != 'user_id'){
                $returnMe[$colname] = $row[$colname];
            }
       }

       return $returnMe;
    }

    function zeroAspectPreferences(){
        $dbh = $this->_dbh;
        global $config;
        $aprefs = $config['tables']['aspect_preferences_table'];
        $dbname = $config['db']['dbname'];
        $userid = $this->_id;

        $colsql = "select column_name from information_schema.columns where table_name='$aprefs' AND table_schema='$dbname'";
        $colres = $dbh->prepare($colsql);
        $colres->execute(array());

        while($colrow = $colres->fetch()){
            $colname = $colrow['column_name'];
            if(substr($colname, 0, 4)=='show'){
                 
              $this->setAspectPreference($colname, 0);
           }
 
      }

    }

    function delete(){
        global $config;
        $users_table = $config['tables']['users_table'];
        $uprefs = $config['tables']['user_preferences_table'];
        $aprefs = $config['tables']['aspect_preferences_table'];
        $user_id = $this->_id;
        $dbh = $this->_dbh;

        $q = "DELETE FROM $users_table WHERE id=?";
        $sth = $dbh->prepare($q);
        $sth->execute(array($user_id));

        $q = "DELETE FROM $uprefs WHERE user_id=?";
        $sth = $dbh->prepare($q);
        $sth->execute(array($user_id));

        $q = "DELETE FROM $aprefs WHERE user_id=?";
        $sth = $dbh->prepare($q);
        $sth->execute(array($user_id));
    }

    static function newUser($newUsername, $newPassword, $newFullname, $newEmail, $newCss, $newAccessLevel, $newTimezone){
 	
        $cryptPassword = crypt($newPassword);
        $dbh = db_connect();        
        global $config;
        $users_table = $config['tables']['users_table'];
        $uprefs = $config['tables']['user_preferences_table'];
        $aprefs = $config['tables']['aspect_preferences_table'];

        $query = "INSERT INTO users VALUES ('',?,?,?,?,?,?,'100',NOW(),?)";
        $sth = $dbh->prepare($query);
        $result = $sth->execute(array($newUsername, $cryptPassword, $newFullname, $newAccessLevel, $newEmail, $newCss, $newTimezone));
                           
        $uid = $dbh->lastInsertId();

        $query2 = "INSERT INTO $uprefs VALUES ()";
        $r2 = $dbh->exec($query2);
        $pref_id = $dbh->lastInsertId();
                
        $query3 = "UPDATE $uprefs SET user_id='$uid' WHERE id='$pref_id'";
        $r3 = $dbh->exec($query3);

        $query2 = "INSERT INTO $aprefs VALUES ()";
        $r2 = $dbh->exec($query2);
        $pref_id = $dbh->lastInsertId();
                
        $query3 = "UPDATE $aprefs SET user_id='$uid' WHERE id='$pref_id'";
        $r3 = $dbh->exec($query3);

        $user = new User($uid);
        $user->setAspectPreference('show_chat','1');
        $user->setAspectPreference('show_newbie','1');
        $user->setAspectPreference('show_nav','1');
        $user->setUserPreference('exit_msg','leaves.');
        $user->setUserPreference('entry_msg','arrives.');
        return $user;
    }

}