RsGitExternalsPlugin
====================

*manage symfony plugins hosted with git*

Installation
------------

  - cd plugins && git clone git://github.com/digitalkaoz/rsGitExternalsPlugin.git
  - activate plugin

Configuration
-------------

create files named *.gitexternals* in your symfony project:

**plugins/.gitexternals**
    
    rsGitExternalsPlugin        git://github.com/digitalkaoz/rsGitExternalsPlugin.git

Usage
-----

**pull or clone**
   
    symfony plugin:git-externals pull

**push**
   
    symfony plugin:git-externals push

TODO
----
  
  - tests   
  - commit   
  - status