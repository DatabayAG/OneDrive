# Changelog
## [2.4.0]
- Change: ILIAS 7 compatibility

## [2.3.0]
- Feature: new file upload (chunked, no upload limit & upload progress bar)
- Feature: log events for upload/rename/delete and display them in a table (tab "Settings", subtab "Events")

## [2.2.3]
- Change: changed Office-Online permission to general 'Edit in online editor' (shared with other cloud plugins) - **might require an ILIAS language reload**

## [2.2.2]
- Fix: 'invalid_token' bug (access tokens too long)

## [2.2.1]
- Fix Docker-ILIAS log

## [2.2.0]
- Feature: (globally) configurable info message (usage information)

## [2.1.0]
- Change: ILIAS 6 support
- Change: dropped ILIAS 5.4 support

## [2.0.1]
- Bugfix: fixed file download
- Bugfix: creating root folder and subfolder at the same time didn't work
- Improvement: better error message when renaming fails
- Improvement: add file type suffix on rename
- Bugfix: Save token per user instead of per object (avoid "not authenticated")
- Bugfix: Added missing permission language variable ("hack" required)

## [2.0.0]
- Feature: "Open in Office Online" will automatically give permissions on document
- Feature: new permission for "Open in Office Online"
- Feature: renaming of folders and objects
- Feature: add file type suffix automatically if missing
- Feature: open base folder in Office Online via 'Actions' dropdown (in top right corner)
- Change: removed display of access token in object settings 
- Wording: different wording in object creation form
- Bugfix: fixed deep links
- Bugfix: catch unsupported characters exception
- Bugfix: catch title beginning with whitespace exception
- Bugfix: fixed objects beginning with an umlaut (created in ILIAS or OneDrive)
- Bugfix: fixed folders beginning with an umlaut (created in ILIAS or OneDrive)
- Bugfix: renaming of folder in OneDrive doesn't lead to an error anymore
- Bugfix: fixed ui representation of folders (less space, like in repository)

## [1.0.3]
- Add changelog

## [1.0.2]
- Update ILIAS support to 5.3 and 5.4
- Drop support for ILIAS 5.2 and below
- Add new permission "Open in Office Online"

## [1.0.1]
- Fix unsupported characters
- Fix cloud object starting with whitespace
- Fix cloud objects starting with umlaut
- Remove unnecessary section in settings
- Fix file upload without specified file extension
- Add proper description of base folder and custom folder on object creation
- Fix error on path rename in OneDrive

## [1.0.0]
- Release

## [0.1.0]
- First version
