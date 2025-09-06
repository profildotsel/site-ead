-- Adicionar coluna video_url na tabela lessons se não existir
ALTER TABLE lessons ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) NULL;

-- Verificar se a coluna order permite NULL e ajustar se necessário
ALTER TABLE lessons MODIFY COLUMN `order` INT NOT NULL DEFAULT 1;