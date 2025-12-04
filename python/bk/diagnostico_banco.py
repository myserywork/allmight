"""
Script para verificar a diferença entre coleta antiga (só licitações)
e coleta nova (completa com itens, arquivos e histórico)
"""

import sqlite3
from datetime import datetime

def verificar_estrutura_banco():
    """Verifica a estrutura atual do banco"""
    print("="*70)
    print("VERIFICAÇÃO DA ESTRUTURA DO BANCO")
    print("="*70)
    
    try:
        conn = sqlite3.connect('licitacoes.db')
        cursor = conn.cursor()
        
        # Listar todas as tabelas
        cursor.execute("""
            SELECT name FROM sqlite_master 
            WHERE type='table' 
            ORDER BY name
        """)
        
        tabelas = [row[0] for row in cursor.fetchall()]
        
        if not tabelas:
            print("❌ Banco vazio! Execute primeiro:")
            print("   python consulta_licitacao_completo.py")
            return
        
        print(f"\n✓ Tabelas encontradas: {len(tabelas)}")
        for tabela in tabelas:
            print(f"  - {tabela}")
        
        # Verificar se tem as novas tabelas
        tabelas_novas = ['licitacao_itens', 'licitacao_arquivos', 'licitacao_historico']
        tem_todas = all(t in tabelas for t in tabelas_novas)
        
        print(f"\n{'='*70}")
        if tem_todas:
            print("✓ BANCO COMPLETO - Tem todas as tabelas novas!")
            print("="*70)
            analisar_dados_completos(cursor)
        else:
            print("⚠️  BANCO ANTIGO - Faltam tabelas!")
            print("="*70)
            print("\nTabelas faltando:")
            for t in tabelas_novas:
                if t not in tabelas:
                    print(f"  ✗ {t}")
            
            print("\nPara atualizar, execute:")
            print("  python consulta_licitacao_completo.py")
        
        conn.close()
        
    except sqlite3.Error as e:
        print(f"❌ Erro ao acessar banco: {e}")

def analisar_dados_completos(cursor):
    """Analisa os dados se o banco estiver completo"""
    
    # Total de licitações
    cursor.execute("SELECT COUNT(*) FROM licitacoes")
    total_licitacoes = cursor.fetchone()[0]
    
    # Total de itens
    cursor.execute("SELECT COUNT(*) FROM licitacao_itens")
    total_itens = cursor.fetchone()[0]
    
    # Total de arquivos
    cursor.execute("SELECT COUNT(*) FROM licitacao_arquivos")
    total_arquivos = cursor.fetchone()[0]
    
    # Total de histórico
    cursor.execute("SELECT COUNT(*) FROM licitacao_historico")
    total_historico = cursor.fetchone()[0]
    
    print(f"\n📊 ESTATÍSTICAS DO BANCO")
    print("="*70)
    print(f"Licitações:     {total_licitacoes:>8,}".replace(',', '.'))
    print(f"Itens:          {total_itens:>8,}".replace(',', '.'))
    print(f"Arquivos:       {total_arquivos:>8,}".replace(',', '.'))
    print(f"Histórico:      {total_historico:>8,}".replace(',', '.'))
    print(f"\nTotal de registros: {(total_licitacoes + total_itens + total_arquivos + total_historico):>8,}".replace(',', '.'))
    
    # Média de itens por licitação
    if total_licitacoes > 0:
        media_itens = total_itens / total_licitacoes
        media_arquivos = total_arquivos / total_licitacoes
        media_historico = total_historico / total_licitacoes
        
        print(f"\n📈 MÉDIAS POR LICITAÇÃO")
        print("="*70)
        print(f"Média de itens:          {media_itens:.1f}")
        print(f"Média de arquivos:       {media_arquivos:.1f}")
        print(f"Média de eventos (hist): {media_historico:.1f}")
    
    # Licitações sem dados detalhados
    cursor.execute("""
        SELECT COUNT(*) FROM licitacoes l
        WHERE NOT EXISTS (
            SELECT 1 FROM licitacao_itens i 
            WHERE i.licitacao_id = l.id
        )
    """)
    sem_itens = cursor.fetchone()[0]
    
    cursor.execute("""
        SELECT COUNT(*) FROM licitacoes l
        WHERE NOT EXISTS (
            SELECT 1 FROM licitacao_arquivos a 
            WHERE a.licitacao_id = l.id
        )
    """)
    sem_arquivos = cursor.fetchone()[0]
    
    cursor.execute("""
        SELECT COUNT(*) FROM licitacoes l
        WHERE NOT EXISTS (
            SELECT 1 FROM licitacao_historico h 
            WHERE h.licitacao_id = l.id
        )
    """)
    sem_historico = cursor.fetchone()[0]
    
    print(f"\n⚠️  LICITAÇÕES SEM DADOS DETALHADOS")
    print("="*70)
    print(f"Sem itens:     {sem_itens:>6} ({sem_itens/total_licitacoes*100:.1f}%)")
    print(f"Sem arquivos:  {sem_arquivos:>6} ({sem_arquivos/total_licitacoes*100:.1f}%)")
    print(f"Sem histórico: {sem_historico:>6} ({sem_historico/total_licitacoes*100:.1f}%)")
    
    # Licitações mais completas
    print(f"\n🏆 TOP 5 LICITAÇÕES MAIS COMPLETAS")
    print("="*70)
    
    cursor.execute("""
        SELECT 
            l.titulo,
            COUNT(DISTINCT i.id) as itens,
            COUNT(DISTINCT a.id) as arquivos,
            COUNT(DISTINCT h.id) as historico,
            (COUNT(DISTINCT i.id) + COUNT(DISTINCT a.id) + COUNT(DISTINCT h.id)) as total
        FROM licitacoes l
        LEFT JOIN licitacao_itens i ON l.id = i.licitacao_id
        LEFT JOIN licitacao_arquivos a ON l.id = a.licitacao_id
        LEFT JOIN licitacao_historico h ON l.id = h.licitacao_id
        GROUP BY l.id
        ORDER BY total DESC
        LIMIT 5
    """)
    
    for idx, (titulo, itens, arqs, hist, total) in enumerate(cursor.fetchall(), 1):
        print(f"\n{idx}. {titulo[:50]}...")
        print(f"   Itens: {itens} | Arquivos: {arqs} | Histórico: {hist} | Total: {total}")
    
    # Data da última atualização
    cursor.execute("SELECT MAX(atualizado_em) FROM licitacoes")
    ultima_atualizacao = cursor.fetchone()[0]
    
    if ultima_atualizacao:
        print(f"\n⏰ ÚLTIMA ATUALIZAÇÃO")
        print("="*70)
        print(f"Data/Hora: {ultima_atualizacao}")
        
        # Calcular tempo desde última atualização
        try:
            dt_atualizacao = datetime.fromisoformat(ultima_atualizacao)
            dt_agora = datetime.now()
            diferenca = dt_agora - dt_atualizacao
            
            horas = int(diferenca.total_seconds() // 3600)
            minutos = int((diferenca.total_seconds() % 3600) // 60)
            
            print(f"Há {horas}h {minutos}min atrás")
        except:
            pass
    
    print(f"\n{'='*70}")

def comparar_com_backup():
    """Compara banco atual com backup (se existir)"""
    print(f"\n{'='*70}")
    print("COMPARAÇÃO COM BACKUP")
    print("="*70)
    
    try:
        # Tentar conectar no backup
        conn_backup = sqlite3.connect('licitacoes_backup.db')
        cursor_backup = conn_backup.cursor()
        
        cursor_backup.execute("SELECT COUNT(*) FROM licitacoes")
        total_backup = cursor_backup.fetchone()[0]
        conn_backup.close()
        
        # Conectar no atual
        conn_atual = sqlite3.connect('licitacoes.db')
        cursor_atual = conn_atual.cursor()
        
        cursor_atual.execute("SELECT COUNT(*) FROM licitacoes")
        total_atual = cursor_atual.fetchone()[0]
        conn_atual.close()
        
        print(f"Licitações no backup: {total_backup}")
        print(f"Licitações no atual:  {total_atual}")
        print(f"Diferença:            {total_atual - total_backup:+d}")
        
    except sqlite3.Error:
        print("ℹ️  Não há backup para comparar")
        print("   (Isso é normal se é a primeira execução)")

def recomendar_acao():
    """Recomenda próxima ação baseado no estado do banco"""
    print(f"\n{'='*70}")
    print("💡 RECOMENDAÇÕES")
    print("="*70)
    
    try:
        conn = sqlite3.connect('licitacoes.db')
        cursor = conn.cursor()
        
        # Verificar se tem as novas tabelas
        cursor.execute("""
            SELECT name FROM sqlite_master 
            WHERE type='table' AND name IN ('licitacao_itens', 'licitacao_arquivos', 'licitacao_historico')
        """)
        
        tabelas_novas = [row[0] for row in cursor.fetchall()]
        
        if len(tabelas_novas) < 3:
            print("\n1️⃣  Execute a coleta completa:")
            print("   python consulta_licitacao_completo.py")
            print("   → Isso vai adicionar itens, arquivos e histórico")
        else:
            cursor.execute("SELECT COUNT(*) FROM licitacoes")
            total_lic = cursor.fetchone()[0]
            
            cursor.execute("SELECT COUNT(*) FROM licitacao_itens")
            total_itens = cursor.fetchone()[0]
            
            if total_lic > 0 and total_itens == 0:
                print("\n⚠️  Você tem licitações mas nenhum item!")
                print("   Execute novamente:")
                print("   python consulta_licitacao_completo.py")
            elif total_lic > 0:
                print("\n✅ Banco completo e funcionando!")
                print("\n📊 Próximos passos:")
                print("   1. Visualizar dados:")
                print("      python visualizar_dados.py")
                print("\n   2. Fazer consultas SQL:")
                print("      Veja: consultas_uteis.sql")
                print("\n   3. Atualizar dados (recomendado semanalmente):")
                print("      python consulta_licitacao_completo.py")
            else:
                print("\n⚠️  Banco vazio!")
                print("   Execute:")
                print("   python consulta_licitacao_completo.py")
        
        conn.close()
        
    except sqlite3.Error:
        print("\n⚠️  Banco não encontrado!")
        print("   Execute:")
        print("   python consulta_licitacao_completo.py")

def main():
    print(f"\n{'='*70}")
    print("DIAGNÓSTICO DO BANCO DE LICITAÇÕES")
    print(f"Data: {datetime.now().strftime('%d/%m/%Y %H:%M:%S')}")
    print("="*70)
    
    verificar_estrutura_banco()
    comparar_com_backup()
    recomendar_acao()
    
    print(f"\n{'='*70}")
    print("✓ Diagnóstico concluído!")
    print("="*70)

if __name__ == "__main__":
    main()
