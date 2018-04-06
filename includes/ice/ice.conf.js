/*************************************************************************
*
* ADOBE SYSTEMS INCORPORATED
* Copyright 2008 Adobe Systems Incorporated
* All Rights Reserved.
*
* NOTICE:  Adobe permits you to use, modify, and distribute this file
* in accordance with the terms of the Adobe license agreement accompanying
* it. If you have received this file from a source other than Adobe, then
* your use, modification, or distribution of it requires the prior written
* permission of Adobe.
*
* AdobePatentID="B564"
* AdobePatentID="B565"
*
**************************************************************************/

if (typeof ICE == 'undefined') {
	ICE = {};
}

/*
 * This setting defines the shortcut for launching the InContext Editing Login dialog on any PC machine.
 * If you want to disable this keyboard shortcut leave an empty value for the option.
 *
 * The shortcut definition must include one or more keyboard modifiers and a character. The available PC
 * keyboard modifier are: CTRL, ALT and SHIFT. All keyboard modifiers and the required character must be
 * separated by "+" (without the quotes).
 *
 * Sample shortcuts:
 *   CTRL+E
 *   CTRL+ALT+Y
 *   CTRL+ALT+SHIFT+W
 *
 * NOTE: Please make sure you do not overwrite any default keyboard shortcut available in your web browser(s).
 */
ICE.USER_LOGIN_PC = "CTRL+E";

/*
 * This setting defines the shortcut for launching the InContext Editing Login dialog on any Macintosh machine
 * If you want to disable this keyboard shortcut leave an empty value for the option.
 *
 * The shortcut definition must include one or more keyboard modifiers and a character. The available Macintosh
 * keyboard modifier are: CMD, CTRL, ALT and SHIFT. All keyboard modifiers and the required character must be
 * separated by "+" (without the quotes).
 *
 * Sample shortcuts:
 *   CTRL+E
 *   CTRL+CMD+ALT+Y
 *   CMD+ALT+SHIFT+W
 *
 * NOTE: Please make sure you do not overwrite any default keyboard shortcut available in your web browser(s).
 */
ICE.USER_LOGIN_MAC = "CMD+E";

