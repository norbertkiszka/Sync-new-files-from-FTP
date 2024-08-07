<?php

/**
 * PHP simple script (CLI) to sync (download new files) from FTP server into local directory.
 * This works in a loop. In every loop run it will try to connect to FTP server and download files with names that are not present in LOCAL_DIRECTORY. Changed files are not synced.
 * This will not work with directories on a FTP server (it will generate syslog warning and continue).
 * Version: I don't care with versioning here.
 */

/*						SETTINGS						*/

define('REMOTE_HOST', '192.168.1.12'); // Hostname or ip adress.
define('REMOTE_PORT', '23'); // Port number (default ftp port is 21).
define('REMOTE_TIMEOUT', '3'); // Timeout in seconds. In local network this value should be low.
define('REMOTE_DIRECTORY', '/'); // Remote directory to sync from.
define('LOCAL_DIRECTORY', '/home/ramzes/Obrazy/ftpremote/'); // Local directory path to store files from a given FTP location.

define('PRINT_DOWNLOADED_FILES', true); // True or false.
define('PRINT_CONNECTION_FAILED', false); // True or false.
define('LOOP_SLEEP', 10); // How many seconds to sleep after each loop run (loop iteration).

/*						END OF SETTINGS						*/

class php_sync_from_ftp
{
	public function mainLoop()
	{
		$this->_remote_addr = REMOTE_HOST . ':' . REMOTE_PORT;
		while(true)
		{
			$this->doSync();
			sleep(LOOP_SLEEP);
		}
	}
	
	protected function doSync()
	{
		try
		{
			$this->remote_connect();
			if(! $this->_connection)
			{
				return;
			}
			
			$files = ftp_nlist($this->_connection, REMOTE_DIRECTORY);
			if(! is_array($files))
			{
				throw new Exception('ftp_nlist failed');
			}
			
			foreach($files as $file)
			{
				if(! file_exists(LOCAL_DIRECTORY . '/' . $file))
				{
					if(PRINT_DOWNLOADED_FILES)
					{
						echo "Downloading file $file\n";
					}
					if(! ftp_get($this->_connection, LOCAL_DIRECTORY . '/' . $file, $file, FTP_BINARY))
					{
						$this->slog("Failed to download $file");
					}
				}
				usleep(1000);
			}
		}
		catch (Exception $e)
		{
			$this->slog($e->getMessage());
		}
		finally
		{
			$this->remote_disconnect();
		}
	}
	
	protected function remote_connect()
	{
		if(! ($this->_connection = ftp_connect(REMOTE_HOST, REMOTE_PORT, REMOTE_TIMEOUT)))
		{
			$mess = 'Failed to connect with ' . $this->_remote_addr;
			
			//throw new Exception($mess);
			if(PRINT_CONNECTION_FAILED)
			{
				echo $mess . "\n";
			}
		}
	}
	
	protected function remote_disconnect()
	{
		if($this->_connection)
		{
			ftp_close($this->_connection);
		}
		$this->_connection = null;
	}
	
	protected function slog($message, $priority = LOG_WARNING)
	{
		openlog(get_class($this), LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog($priority, $message);
		closelog();
	}
}

$o = new php_sync_from_ftp;
$o->mainLoop();
