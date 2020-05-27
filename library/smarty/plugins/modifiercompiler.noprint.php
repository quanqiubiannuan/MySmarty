<?php

/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifierCompiler
 */
/**
 * Smarty noprint modifier plugin
 * Type: modifier
 * Name: noprint
 * Purpose: return an empty string
 *
 * @return string with compiled code
 * @author Uwe Tews
 */
function smarty_modifiercompiler_noprint()
{
    return "''";
}
