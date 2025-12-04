<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'admin/dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Admin Routes
$route['admin'] = 'admin/dashboard';
$route['admin/dashboard'] = 'admin/dashboard';
$route['admin/licitacoes'] = 'admin/licitacoes';
$route['admin/licitacoes/abertas'] = 'admin/licitacoes';
$route['admin/analises'] = 'admin/analises';
$route['admin/analise/(:any)'] = 'admin/analisar_licitacao/$1';
$route['admin/analisar_licitacao/(:any)'] = 'admin/analisar_licitacao/$1';
$route['admin/api/analise_detalhes/(:any)'] = 'admin/api_analise_detalhes/$1';
$route['admin/chat_ia'] = 'admin/chat_ia';
$route['admin/processar_lote_ia'] = 'admin/processar_lote_ia';
$route['admin/matches'] = 'admin/matches';
$route['admin/match/(:any)'] = 'admin/match_detalhes/$1';
$route['admin/gerar_matches/(:any)'] = 'admin/gerar_matches/$1';
$route['admin/match_atualizar_status'] = 'admin/match_atualizar_status';
$route['admin/licitacao/(:any)'] = 'admin/licitacao_detalhes/$1';
$route['admin/empresas'] = 'admin/empresas';
$route['admin/empresa/nova'] = 'admin/empresa_nova';
$route['admin/empresa/editar/(:any)'] = 'admin/empresa_editar/$1';
$route['admin/empresa/salvar'] = 'admin/empresa_salvar';
$route['admin/empresa/deletar/(:any)'] = 'admin/empresa_deletar/$1';
$route['admin/empresa/toggle/(:any)'] = 'admin/empresa_toggle_status/$1';
$route['admin/api/buscar-cep'] = 'admin/buscar_cep';
$route['admin/api/buscar-cnpj'] = 'admin/buscar_cnpj';
$route['admin/matches'] = 'admin/matches';
$route['admin/analises'] = 'admin/analises';
$route['admin/propostas'] = 'admin/propostas';
$route['admin/proposta/nova/(:any)'] = 'admin/proposta_nova/$1';
$route['admin/proposta/editar/(:any)'] = 'admin/proposta_editar/$1';
$route['admin/proposta/salvar'] = 'admin/proposta_salvar';
$route['admin/proposta/preview/(:any)'] = 'admin/proposta_preview/$1';
$route['admin/proposta/gerar_ia/(:any)'] = 'admin/proposta_gerar_ia/$1';
$route['admin/proposta/exportar/pdf/(:any)'] = 'admin/proposta_exportar_pdf/$1';
$route['admin/proposta/exportar/docx/(:any)'] = 'admin/proposta_exportar_docx/$1';
$route['admin/proposta/deletar/(:any)'] = 'admin/proposta_deletar/$1';
$route['admin/proposta/atualizar_status'] = 'admin/proposta_atualizar_status';
$route['admin/relatorios'] = 'admin/relatorios';
$route['admin/configuracoes'] = 'admin/configuracoes';

// Monitoramento e Keywords
$route['admin/monitoramento'] = 'admin/monitoramento';
$route['admin/executar_matching'] = 'admin/executar_matching';
$route['admin/gerar_keywords_ia'] = 'admin/gerar_keywords_ia';
$route['admin/alerta_visualizar/(:num)'] = 'admin/alerta_visualizar/$1';
$route['admin/alerta_descartar/(:num)'] = 'admin/alerta_descartar/$1';
$route['admin/alerta_detalhes/(:num)'] = 'admin/alerta_detalhes/$1';
