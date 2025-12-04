"""
Script para verificar se o ambiente MySQL está pronto para o AllMight
"""

import mysql.connector
from mysql.connector import Error

def verificar_mysql():
    """Verifica se o MySQL está acessível"""
    print("="*70)
    print("🔍 VERIFICAÇÃO DO AMBIENTE MYSQL - ALLMIGHT")
    print("="*70)
    
    try:
        # Tentar conectar sem especificar banco
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password=''
        )
        
        print("\n✓ MySQL está rodando!")
        print(f"  Versão: {conn.get_server_info()}")
        
        cursor = conn.cursor()
        
        # Verificar se banco allmight existe
        cursor.execute("SHOW DATABASES LIKE 'allmight'")
        banco_existe = cursor.fetchone()
        
        if not banco_existe:
            print("\n❌ Banco 'allmight' NÃO existe!")
            print("\n📋 INSTRUÇÕES:")
            print("1. Abra o phpMyAdmin: http://localhost/phpmyadmin")
            print("2. Clique em 'Novo' para criar banco")
            print("3. Nome: allmight")
            print("4. Collation: utf8mb4_unicode_ci")
            print("5. Clique em 'Criar'")
            print("6. Vá em 'Importar' e escolha 'schema_allmight_mysql.sql'")
            cursor.close()
            conn.close()
            return False
        
        print("✓ Banco 'allmight' existe!")
        
        # Conectar ao banco
        cursor.close()
        conn.close()
        
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='',
            database='allmight'
        )
        
        cursor = conn.cursor(dictionary=True)
        
        # Verificar tabelas
        cursor.execute("SHOW TABLES")
        tabelas = [list(t.values())[0] for t in cursor.fetchall()]
        
        tabelas_esperadas = [
            'empresas',
            'perfis_empresa',
            'documentos_empresa',
            'projetos_empresa',
            'fontes_licitacao',
            'licitacoes',
            'licitacao_itens',
            'licitacao_arquivos',
            'licitacao_historico',
            'matches',
            'analises_comerciais',
            'propostas',
            'proposta_itens',
            'usuarios',
            'cron_logs',
            'notificacoes',
            'configuracoes'
        ]
        
        print(f"\n📊 Tabelas encontradas: {len(tabelas)}")
        
        tabelas_faltando = [t for t in tabelas_esperadas if t not in tabelas]
        
        if tabelas_faltando:
            print(f"\n⚠️  Faltam {len(tabelas_faltando)} tabelas:")
            for t in tabelas_faltando:
                print(f"  - {t}")
            print("\n📋 Execute o arquivo 'schema_allmight_mysql.sql' no phpMyAdmin")
            cursor.close()
            conn.close()
            return False
        
        print("✓ Todas as tabelas principais existem!")
        
        # Verificar fonte PNCP
        cursor.execute("SELECT * FROM fontes_licitacao WHERE tipo_portal = 'PNCP'")
        fonte = cursor.fetchone()
        
        if not fonte:
            print("\n⚠️  Fonte PNCP não encontrada!")
            print("     Execute o arquivo 'schema_allmight_mysql.sql' completo")
            cursor.close()
            conn.close()
            return False
        
        print(f"✓ Fonte PNCP configurada - ID: {fonte['id']}")
        print(f"  Nome: {fonte['nome']}")
        print(f"  URL: {fonte['url_base']}")
        
        # Verificar configurações
        cursor.execute("SELECT COUNT(*) as total FROM configuracoes")
        config_count = cursor.fetchone()['total']
        print(f"✓ Configurações: {config_count} registros")
        
        # Verificar views
        cursor.execute("SHOW FULL TABLES WHERE Table_type = 'VIEW'")
        views = cursor.fetchall()
        print(f"✓ Views: {len(views)} criadas")
        for view in views:
            print(f"  - {list(view.values())[0]}")
        
        # Estatísticas do banco
        print(f"\n📈 ESTATÍSTICAS DO BANCO:")
        
        cursor.execute("SELECT COUNT(*) as total FROM licitacoes")
        total_lic = cursor.fetchone()['total']
        print(f"  Licitações: {total_lic}")
        
        if total_lic > 0:
            cursor.execute("SELECT COUNT(DISTINCT uf) as total_ufs FROM licitacoes")
            total_ufs = cursor.fetchone()['total_ufs']
            print(f"  Estados: {total_ufs}")
            
            cursor.execute("SELECT COUNT(*) as total FROM licitacao_itens")
            total_itens = cursor.fetchone()['total']
            print(f"  Itens: {total_itens}")
            
            cursor.execute("SELECT COUNT(*) as total FROM licitacao_arquivos")
            total_arquivos = cursor.fetchone()['total']
            print(f"  Arquivos: {total_arquivos}")
            
            cursor.execute("SELECT COUNT(*) as total FROM licitacao_historico")
            total_hist = cursor.fetchone()['total']
            print(f"  Históricos: {total_hist}")
        
        cursor.close()
        conn.close()
        
        print("\n" + "="*70)
        print("✅ AMBIENTE PRONTO PARA USO!")
        print("="*70)
        print("\n🚀 Próximo passo:")
        print("   python consulta_licitacao_mysql.py")
        print("="*70 + "\n")
        
        return True
        
    except Error as e:
        print(f"\n❌ ERRO: {e}")
        
        if "Can't connect" in str(e):
            print("\n📋 SOLUÇÃO:")
            print("1. Abra o XAMPP Control Panel")
            print("2. Clique em 'Start' no MySQL")
            print("3. Aguarde o MySQL iniciar (luz verde)")
            print("4. Execute este script novamente")
        
        elif "Access denied" in str(e):
            print("\n📋 SOLUÇÃO:")
            print("1. Verifique a senha do MySQL no XAMPP")
            print("2. Se houver senha, edite o arquivo:")
            print("   consulta_licitacao_mysql.py")
            print("   Linha: MYSQL_CONFIG = {'password': 'SUA_SENHA'}")
        
        elif "Unknown database" in str(e):
            print("\n📋 SOLUÇÃO:")
            print("1. Abra: http://localhost/phpmyadmin")
            print("2. Crie o banco 'allmight'")
            print("3. Execute 'schema_allmight_mysql.sql'")
        
        print()
        return False

if __name__ == "__main__":
    verificar_mysql()
