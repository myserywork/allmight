"""
Script para visualizar e consultar dados coletados do PNCP
Mostra exemplos de como acessar itens, arquivos e histórico
"""

import sqlite3
import json
from datetime import datetime

def conectar_banco():
    """Conecta ao banco de dados"""
    return sqlite3.connect('licitacoes.db')

def formatar_moeda(valor):
    """Formata valor em reais"""
    if valor is None:
        return "R$ 0,00"
    return f"R$ {valor:,.2f}".replace(',', 'X').replace('.', ',').replace('X', '.')

def listar_estatisticas():
    """Mostra estatísticas gerais do banco"""
    conn = conectar_banco()
    cursor = conn.cursor()
    
    print("="*70)
    print("ESTATÍSTICAS DO BANCO DE DADOS")
    print("="*70)
    
    # Total de licitações
    cursor.execute('SELECT COUNT(*) FROM licitacoes')
    total_licitacoes = cursor.fetchone()[0]
    print(f"✓ Total de licitações: {total_licitacoes}")
    
    # Total de itens
    cursor.execute('SELECT COUNT(*) FROM licitacao_itens')
    total_itens = cursor.fetchone()[0]
    print(f"✓ Total de itens: {total_itens}")
    
    # Total de arquivos
    cursor.execute('SELECT COUNT(*) FROM licitacao_arquivos')
    total_arquivos = cursor.fetchone()[0]
    print(f"✓ Total de arquivos: {total_arquivos}")
    
    # Total de histórico
    cursor.execute('SELECT COUNT(*) FROM licitacao_historico')
    total_historico = cursor.fetchone()[0]
    print(f"✓ Total de eventos no histórico: {total_historico}")
    
    # Licitações por UF
    print(f"\n{'='*70}")
    print("LICITAÇÕES POR ESTADO")
    print("="*70)
    cursor.execute('''
        SELECT uf, COUNT(*) as total
        FROM licitacoes
        GROUP BY uf
        ORDER BY total DESC
    ''')
    
    for uf, total in cursor.fetchall():
        print(f"{uf}: {total:>4} licitações")
    
    # Top 5 licitações com mais itens
    print(f"\n{'='*70}")
    print("TOP 5 LICITAÇÕES COM MAIS ITENS")
    print("="*70)
    cursor.execute('''
        SELECT 
            l.titulo,
            l.orgao_nome,
            l.uf,
            COUNT(i.id) as total_itens
        FROM licitacoes l
        INNER JOIN licitacao_itens i ON l.id = i.licitacao_id
        GROUP BY l.id
        ORDER BY total_itens DESC
        LIMIT 5
    ''')
    
    for idx, (titulo, orgao, uf, total) in enumerate(cursor.fetchall(), 1):
        print(f"\n{idx}. {titulo[:50]}...")
        print(f"   Órgão: {orgao}")
        print(f"   UF: {uf}")
        print(f"   Total de itens: {total}")
    
    conn.close()
    print(f"\n{'='*70}\n")

def mostrar_detalhes_licitacao(licitacao_id=None):
    """Mostra detalhes completos de uma licitação"""
    conn = conectar_banco()
    cursor = conn.cursor()
    
    # Se não passou ID, pega a primeira
    if not licitacao_id:
        cursor.execute('SELECT id FROM licitacoes LIMIT 1')
        resultado = cursor.fetchone()
        if not resultado:
            print("❌ Nenhuma licitação encontrada no banco!")
            return
        licitacao_id = resultado[0]
    
    # Buscar licitação
    cursor.execute('''
        SELECT 
            titulo, descricao, orgao_nome, unidade_nome,
            uf, municipio_nome, modalidade_licitacao_nome,
            situacao_nome, data_publicacao_pncp, 
            data_inicio_vigencia, data_fim_vigencia,
            valor_global, url_navegador
        FROM licitacoes
        WHERE id = ?
    ''', (licitacao_id,))
    
    licitacao = cursor.fetchone()
    if not licitacao:
        print(f"❌ Licitação {licitacao_id} não encontrada!")
        return
    
    (titulo, descricao, orgao_nome, unidade_nome, uf, municipio_nome,
     modalidade, situacao, data_pub, data_inicio, data_fim, 
     valor_global, url_navegador) = licitacao
    
    print("="*70)
    print("DETALHES DA LICITAÇÃO")
    print("="*70)
    print(f"Título: {titulo}")
    print(f"Órgão: {orgao_nome}")
    print(f"Unidade: {unidade_nome}")
    print(f"Local: {municipio_nome}/{uf}")
    print(f"Modalidade: {modalidade}")
    print(f"Situação: {situacao}")
    print(f"Publicação: {data_pub}")
    print(f"Vigência: {data_inicio} até {data_fim}")
    if valor_global:
        print(f"Valor Global: {formatar_moeda(valor_global)}")
    print(f"URL: {url_navegador}")
    print(f"\nDescrição:\n{descricao}")
    
    # Buscar itens
    print(f"\n{'='*70}")
    print("ITENS DA LICITAÇÃO")
    print("="*70)
    
    cursor.execute('''
        SELECT 
            numero_item, descricao, quantidade, unidade_medida,
            valor_unitario_estimado, valor_total, material_ou_servico_nome
        FROM licitacao_itens
        WHERE licitacao_id = ?
        ORDER BY numero_item
    ''', (licitacao_id,))
    
    itens = cursor.fetchall()
    if itens:
        for num, desc, qtd, un, val_unit, val_total, tipo in itens:
            print(f"\nItem {num} ({tipo})")
            print(f"  Descrição: {desc[:100]}...")
            print(f"  Quantidade: {qtd} {un}")
            if val_unit:
                print(f"  Valor unitário: {formatar_moeda(val_unit)}")
            if val_total:
                print(f"  Valor total: {formatar_moeda(val_total)}")
    else:
        print("Nenhum item encontrado.")
    
    # Buscar arquivos
    print(f"\n{'='*70}")
    print("ARQUIVOS/DOCUMENTOS")
    print("="*70)
    
    cursor.execute('''
        SELECT 
            titulo, tipo_documento_nome, data_publicacao_pncp, url
        FROM licitacao_arquivos
        WHERE licitacao_id = ?
        ORDER BY data_publicacao_pncp DESC
    ''', (licitacao_id,))
    
    arquivos = cursor.fetchall()
    if arquivos:
        for titulo, tipo, data, url in arquivos:
            print(f"\n📄 {titulo}")
            print(f"   Tipo: {tipo}")
            print(f"   Publicado em: {data}")
            print(f"   URL: {url}")
    else:
        print("Nenhum arquivo encontrado.")
    
    # Buscar histórico
    print(f"\n{'='*70}")
    print("HISTÓRICO DE ALTERAÇÕES")
    print("="*70)
    
    cursor.execute('''
        SELECT 
            log_manutencao_data_inclusao, usuario_nome,
            tipo_log_manutencao_nome, categoria_log_manutencao_nome,
            item_numero
        FROM licitacao_historico
        WHERE licitacao_id = ?
        ORDER BY log_manutencao_data_inclusao DESC
        LIMIT 20
    ''', (licitacao_id,))
    
    historico = cursor.fetchall()
    if historico:
        for data, usuario, tipo, categoria, item_num in historico:
            print(f"\n📅 {data}")
            print(f"   Usuário: {usuario}")
            print(f"   Ação: {tipo}")
            print(f"   Categoria: {categoria}")
            if item_num:
                print(f"   Item: {item_num}")
    else:
        print("Nenhum evento no histórico.")
    
    conn.close()
    print(f"\n{'='*70}\n")

def buscar_por_produto(termo_busca):
    """Busca licitações que contenham determinado produto"""
    conn = conectar_banco()
    cursor = conn.cursor()
    
    print("="*70)
    print(f"BUSCANDO LICITAÇÕES COM: '{termo_busca}'")
    print("="*70)
    
    cursor.execute('''
        SELECT DISTINCT
            l.titulo,
            l.orgao_nome,
            l.uf,
            l.municipio_nome,
            l.url_navegador,
            i.descricao as item_descricao,
            i.quantidade,
            i.unidade_medida,
            i.valor_total
        FROM licitacoes l
        INNER JOIN licitacao_itens i ON l.id = i.licitacao_id
        WHERE UPPER(i.descricao) LIKE ?
        ORDER BY l.data_publicacao_pncp DESC
        LIMIT 10
    ''', (f'%{termo_busca.upper()}%',))
    
    resultados = cursor.fetchall()
    
    if resultados:
        for idx, (titulo, orgao, uf, municipio, url, desc, qtd, un, val) in enumerate(resultados, 1):
            print(f"\n{idx}. {titulo[:50]}...")
            print(f"   Órgão: {orgao}")
            print(f"   Local: {municipio}/{uf}")
            print(f"   Item: {desc[:80]}...")
            print(f"   Quantidade: {qtd} {un}")
            if val:
                print(f"   Valor: {formatar_moeda(val)}")
            print(f"   URL: {url}")
    else:
        print(f"\nNenhuma licitação encontrada com '{termo_busca}'")
    
    conn.close()
    print(f"\n{'='*70}\n")

def menu_principal():
    """Menu interativo"""
    while True:
        print("\n" + "="*70)
        print("SISTEMA DE CONSULTA - LICITAÇÕES PNCP")
        print("="*70)
        print("1. Ver estatísticas gerais")
        print("2. Ver detalhes de uma licitação")
        print("3. Buscar por produto/item")
        print("4. Listar últimas licitações")
        print("0. Sair")
        print("="*70)
        
        opcao = input("\nEscolha uma opção: ").strip()
        
        if opcao == "1":
            listar_estatisticas()
            
        elif opcao == "2":
            licitacao_id = input("Digite o ID da licitação (Enter para ver a primeira): ").strip()
            if not licitacao_id:
                licitacao_id = None
            mostrar_detalhes_licitacao(licitacao_id)
            
        elif opcao == "3":
            termo = input("Digite o termo de busca (ex: AÇÚCAR, COMPUTADOR): ").strip()
            if termo:
                buscar_por_produto(termo)
            
        elif opcao == "4":
            conn = conectar_banco()
            cursor = conn.cursor()
            
            print("\n" + "="*70)
            print("ÚLTIMAS 10 LICITAÇÕES")
            print("="*70)
            
            cursor.execute('''
                SELECT id, titulo, orgao_nome, uf, data_publicacao_pncp
                FROM licitacoes
                ORDER BY data_publicacao_pncp DESC
                LIMIT 10
            ''')
            
            for idx, (id_lic, titulo, orgao, uf, data) in enumerate(cursor.fetchall(), 1):
                print(f"\n{idx}. {titulo[:50]}...")
                print(f"   ID: {id_lic}")
                print(f"   Órgão: {orgao}")
                print(f"   UF: {uf}")
                print(f"   Data: {data}")
            
            conn.close()
            print("\n" + "="*70)
            
        elif opcao == "0":
            print("\n✓ Até logo!")
            break
            
        else:
            print("\n❌ Opção inválida!")

if __name__ == "__main__":
    try:
        # Verificar se banco existe
        conn = sqlite3.connect('licitacoes.db')
        cursor = conn.cursor()
        cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
        tabelas = [t[0] for t in cursor.fetchall()]
        conn.close()
        
        if not tabelas:
            print("❌ Banco de dados vazio!")
            print("Execute primeiro: python consulta_licitacao_completo.py")
        else:
            menu_principal()
            
    except sqlite3.Error as e:
        print(f"❌ Erro ao acessar banco: {e}")
        print("Execute primeiro: python consulta_licitacao_completo.py")
