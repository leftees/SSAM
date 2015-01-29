SSAM
====

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DanielRuf/SSAM/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DanielRuf/SSAM/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/DanielRuf/SSAM/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/DanielRuf/SSAM/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/DanielRuf/SSAM/badges/build.png?b=master)](https://scrutinizer-ci.com/g/DanielRuf/SSAM/build-status/master)

[![Build Status](https://travis-ci.org/DanielRuf/SSAM.svg?branch=master)](https://travis-ci.org/DanielRuf/SSAM)

security oriented fork of SSAM (Simple Site Audit Multisite) simplesiteaudit.terryheffernan.net - early WIP


##Todo (no structural changes needed)
- [ ] migrate from MySQL to PDO (removes the deprecated notices)
- [ ] use a config.sample.php file for individual settings at setup
- [ ] generate a unique key for the encryption/decryption
- [ ] move often used methods to another file and include it (DRY)
- [ ] comment/document all methods (preparation for PHPDoc)
- [ ] use Twitter Bootstrap for a responsive UI
- [ ] find a new name and domain for the fork
- [x] check minimum required PHP version with https://github.com/llaville/php-compat-info `php phpcompatinfo-3.7.0.phar analyser:run --alias current`
- [ ] run an OWASP ZAP scan on a preinstalled version
- [ ] use the PHPMailer library or own class instead of the mail() method for custom mail settings (via SMTP)
- [x] we do not die(), we just exit()

##Planned (structural changes needed)
- [ ] create a dashboard for an overview of all monitored websites
- [ ] integrate code for scanning files for webshells, malware and other malicious (hidden/obfuscated) code
- [ ] backup function for settings, websites and so on
- [ ] remote deploy of solutions/firewalls like [NinjaFirewall](ninjafirewall.com)
- [ ] remote emergency functions (maintenance mode, admin user)
- [ ] use the MVC pattern and PSR
- [ ] update utility (check, update, upgrade, ...)
- [ ] support for SSH/SFTP and FTPS
- [ ] user management system with login and logout functions, rights and so on
- [ ] use semantic versioning (SemVer)
- [ ] generate statistics from database
- [ ] use MySQL query caching
- [ ] monitor databases for changes
- [ ] translate strings and use constants
- [ ] send more mails, when something fails (if enabled)
- [ ] improve database (use InnoDB as engine, set default charset to UTF-8)
- [ ] use prepared statements
- [ ] scan for CVE
- [ ] scan WP plugins for vunlerabilities and exploits
- [ ] add news section (per website) with RSS feeds (manage, show, import, export)
- [ ] export data/views to CSV, TSV, JSON, XML and PDF
- [ ] add uptime monitor
- [ ] monitor mailboxes for spam and malware/viruses
