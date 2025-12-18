-- Atualizar senha do usuário admin
UPDATE usuarios 
SET senha_hash = '$2y$10$B4PRfvRhT.esiawwjdrTreP0x6VyYFhqQg7Djd54tpCRY92UeS6ye'
WHERE email = 'admin@allmight.com';

SELECT 'Senha atualizada com sucesso!' as status;
SELECT id, nome, email, LEFT(senha_hash, 20) as senha_hash_inicio FROM usuarios WHERE email = 'admin@allmight.com';
