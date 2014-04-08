function logger_js_toggle(_id)
{
    if(document.getElementById(_id).style.display == 'block')
        document.getElementById(_id).style.display = 'none';
    else
        document.getElementById(_id).style.display = 'block';
}

function logger_js_close_panels(_id)
{
    if(_id != 'logger_block_log')       document.getElementById('logger_block_log').style.display       = 'none';
    if(_id != 'logger_block_info')      document.getElementById('logger_block_info').style.display      = 'none';
    if(_id != 'logger_block_error')     document.getElementById('logger_block_error').style.display     = 'none';
    if(_id != 'logger_block_database')  document.getElementById('logger_block_database').style.display  = 'none';
    if(_id != 'logger_block_router')    document.getElementById('logger_block_router').style.display    = 'none';
    if(_id != 'logger_block_cache')     document.getElementById('logger_block_cache').style.display     = 'none';
}

function logger_js_show_panel(_id)
{
    logger_js_close_panels(_id);
    logger_js_toggle(_id);
}