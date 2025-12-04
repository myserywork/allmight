import mysql.connector

# Teste simples
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='allmight'
)

cursor = conn.cursor()

# Testar se consegue inserir
import uuid
test_id = str(uuid.uuid4())

sql = """
INSERT INTO licitacoes (
    id, fonte_id, id_externo, titulo, orgao_nome,
    modalidade, situacao, ativo,
    data_insercao, data_atualizacao
) VALUES (
    %s, 1, 'TESTE', 'Teste Licitação', 'Órgão Teste',
    'Pregão', 'Teste', TRUE,
    NOW(), NOW()
)
"""

try:
    cursor.execute(sql, (test_id,))
    conn.commit()
    print(f"✓ Inserção OK! ID: {test_id}")
    
    # Contar
    cursor.execute("SELECT COUNT(*) FROM licitacoes")
    total = cursor.fetchone()[0]
    print(f"✓ Total no banco: {total}")
    
    # Deletar teste
    cursor.execute("DELETE FROM licitacoes WHERE id = %s", (test_id,))
    conn.commit()
    print("✓ Teste deletado")
    
except Exception as e:
    print(f"❌ Erro: {e}")
    conn.rollback()

cursor.close()
conn.close()
