<?php

/*

PHP API wrapper for AhsayOBS. Version 1.10

Copyright (C)  2015
Hannes Van de Vel (h@nnes.be),
Richard Bishop (ahsayapi@uchange.co.uk).


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

*/
class AhsayApiWrapper
{
    public $serverAddress;
    public $serverPort;
    public $serverAdminUsername;
    public $serverAdminPassword;
    public $debug;
    public $error;

    /*
    Note:
    All times (user added, backupset last run, completed etc) are in the form of Unix timestamps.  In the case
    of Java this is the
    number of milliseconds since Jan 1st 1970; though PHP counts this as seconds since Jan 1st 1970.  The
    solution is to disregard
    the final 3 digits of the value output by OBS
    */

    // Constructor
    public function AhsayApiWrapper($server, $port, $username, $password)
    {
        $this->serverAddress = $server;
        $this->serverPort = $port;
        $this->serverAdminUsername = $username;
        $this->serverAdminPassword = $password;
        $this->debug;
    }

    // Enable/disable debugging
    public function debug($which)
    {
        $this->debug = $which;
    }

    // Authenticate a user against OBS
    public function authenticateUser($username, $password)
    {
        $this->debuglog("Authenticate user $username");

        $url = '/obs/api/AuthUser.do?';
        $url .= 'LoginName='.$username.'&Password='.$password;
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 0, 3) == 'err') {
            $this->debuglog("Authenticate user failed $result");
            $this->error = $result;

            return false;
        } else {
            return 'OK';
        }
    }

    // Get a particular user
    public function getUser($username)
    {
        $this->debuglog("Getting user '$username'");

        $url = "/obs/api/GetUser.do?LoginName=$username";
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 0, 3) == 'err') {
            $this->debuglog("No user details found for '$username'");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Get an array of all users
    public function getUsers()
    {
        $this->debuglog('Getting user list');

        $url = '/obs/api/ListUsers.do';
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Doing getUsers() failed $result");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Get all backup sets for a particular user
    public function getUserBackupSets($username)
    {
        $this->debuglog("Getting backup sets for user '$username'");

        $url = "/obs/api/ListBackupSets.do?LoginName=$username";
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Problem during getUserBackupSets() for '$username'");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Get storage statistics for a particular user
    public function getUserStorageStats($username, $date)
    {
        $this->debuglog("Getting storage stats for user '$username'");

        $url = "/obs/api/GetUserStorageStat.do?LoginName=$username&YearMonth=$date";
        $this->debuglog($url);
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Problem during getUserStorageStats() for '$username'");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Get all backup jobs for a particular user
    public function getUserBackupJobs($username)
    {
        $this->debuglog("Getting backup jobs for user '$username'");

        $url = "/obs/api/ListBackupJobs.do?LoginName=$username";
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Problem during getUserBackupJobs() for '$username'");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Get all backup jobs for a particular user, limited to a particular backup set
    public function getBackupJobsForSet($username, $backupset)
    {
        $this->debuglog("Getting backup jobs for user '$username', for backup set with id '$backupset'");

        $url = "/obs/api/ListBackupJobs.do?LoginName=$username";
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Problem during getBackupJobsForSet() for '$username', for backup set with id '$backupset'");
            $this->error = $result;

            return false;
        } else {
            $data = $this->xmlToArray($result);

            foreach ($data['BackupSet'] as $set) {
                // If this is the backupset we are interested in
                if ($set['@attributes']['ID'] == $backupset) {
                    return $set;
                }
            }

            // If we get to here then that backup set obviously doesn't exist!
            $this->debuglog("Problem doing getBackupJobsForSet() - looks like set '$backupset' doesn't exist");

            return false;
        }
    }

    // Get the IDs of each backup job for this set in reverse order
    public function getBackupSetJobIds($username, $backupset, $rev = false)
    {
        $backup_sets = array();

        $this->debuglog("Getting list of backup job ids for user '$username', for backup set with id '$backupset'");

        // Get a list of all backup jobs for this backup set
        $jobs = $this->getBackupJobsForSet($username, $backupset);
        if ($jobs == false) {
            $this->debuglog("Could not run getUserBackupJobsForSet() in getBackupSetJobIds() for backup set id '$backupset'");

            return false;
        }

        // Go through each job id
        foreach ($jobs['BackupJob'] as $job) {
            $backup_jobs[] = $job['@attributes']['ID'];
        }

        // Sort in reverse?
        if ($rev != false) {
            rsort($backup_jobs);
        } else {
            sort($backup_jobs);
        }

        return $backup_jobs;
    }

    // Get the ID of the most recent job for this backup set
    public function getMostRecentBackupJob($username, $backupset)
    {
        $this->debuglog("Running getMostRecentBackupJob() for backup set with id '$backupset'");

        // Get a list of all backup jobs for this backup set (in reverse order)
        $jobs = $this->getBackupSetJobIds($username, $backupset, true);
        if ($jobs == false) {
            $this->debuglog("Could not run getBackupSetJobIds() in getMostRecentBackupJob() for backup set id '$backupset'");

            return false;
        }

        // Return just the most recent
        return $jobs[0];
    }

    // Get all backup jobs for a particular user
    public function getUserBackupJobDetails($username, $backupset, $backupjob)
    {
        $this->debuglog("Getting backup job details for user '$username', job id '$backupjob'");

        $url = "/obs/api/GetBackupJobReport.do?LoginName=$username&BackupSetID=$backupset&BackupJobID=$backupjob";
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Problem during getUserBackupJobDetails() for '$username', job id '$backupjob'");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Get details on a particular backup set
    public function getUserBackupSet($username, $setid)
    {
        $this->debuglog("Getting details for backup set with id '$setid' for user '$username'");

        $url = "/obs/api/GetBackupSet.do?LoginName=$username&BackupSetID=$setid";
        $result = $this->__runQuery($url);

        // If that didn't happen
        if (substr($result, 1, 3) == 'err') {
            $this->debuglog("Problem during getUserBackupSet() for $username");
            $this->error = $result;

            return false;
        } else {
            return $this->xmlToArray($result);
        }
    }

    // Run an API query against OBS
    public function __runQuery($url)
    {
        $url = 'http://'.$this->serverAddress.':'.$this->serverPort.$url;
        // If this URL already has a query string
        if (strstr($url, '?')) {
            $url .= '&SysUser='.$this->serverAdminUsername.'&SysPwd='.$this->serverAdminPassword;
        } else {
            $url .= '?SysUser='.$this->serverAdminUsername.'&SysPwd='.$this->serverAdminPassword;
        }
        $this->debuglog("Trying $url");
        $result = file_get_contents($url);

        return $result;
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    private function xmlToArray($xmlString)
    {
        $xml = simplexml_load_string($xmlString);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return $array;
    }

    // Debug logging
    public function debuglog($message)
    {
        if ($this->debug) {
            printf("%s\n", $message);
        }
    }
}
