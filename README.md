/**
 * PHP simple script (CLI) to sync (download new files) from FTP server into local directory.
 * This works in a loop. In every loop run it will try to connect to FTP server and download files with names that are not present in LOCAL_DIRECTORY. Changed files are not synced.
 * <b>This will not work with directories on a FTP server</b> (it will generate syslog warning and continue).
 * Version: I don't care with versioning here.
 */

Made for and tested with tcpsvd+ftpd on a embedded machine (oscilloscope Rigol DHO924S).
