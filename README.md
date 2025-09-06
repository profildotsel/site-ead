# Sistema EAD - Plataforma de Educação à Distância

Um sistema completo de ensino à distância desenvolvido em PHP e MySQL com interface responsiva em Bootstrap.

## Características Principais

- **Multi-usuários**: Sistema com 3 níveis de acesso (Administrador, Instrutor, Estudante)
- **Multi-professores**: Múltiplos instrutores podem criar e gerenciar seus próprios cursos
- **Gerenciamento de Cursos**: Criação, edição e organização de cursos e aulas
- **Sistema de Inscrições**: Alunos podem se inscrever nos cursos disponíveis
- **Interface Responsiva**: Design adaptável para desktop e mobile
- **Controle de Acesso**: Sistema robusto de autenticação e autorização

## Estrutura do Sistema

### Níveis de Usuário

1. **Administrador**
   - Gerenciar todos os usuários
   - Visualizar todos os cursos
   - Acesso completo ao sistema
   - Relatórios e estatísticas

2. **Instrutor**
   - Criar e gerenciar seus próprios cursos
   - Criar e organizar aulas
   - Visualizar alunos inscritos
   - Gerenciar conteúdo dos cursos

3. **Estudante**
   - Visualizar cursos disponíveis
   - Inscrever-se em cursos
   - Acessar aulas dos cursos inscritos
   - Acompanhar progresso

### Funcionalidades

- **Dashboard personalizado** para cada tipo de usuário
- **Gestão de cursos** com status (rascunho, publicado, arquivado)
- **Sistema de aulas** com conteúdo HTML
- **Navegação entre aulas** com controle de progresso
- **Interface intuitiva** com navegação por sidebar
- **Sistema de notificações** para ações importantes

## Instalação

1. **Banco de Dados**
   ```sql
   # Execute o arquivo SQL fornecido para criar a estrutura
   mysql -u root -p < /workspace/uploads/ead_platform.sql
   ```

2. **Configuração**
   - Edite o arquivo `config/database.php` com suas credenciais do banco
   - Configure o servidor web para apontar para a pasta do projeto

3. **Usuários de Teste**
   - **Admin**: usuário `admin`, senha `admin123`
   - **Professor**: usuário `professor`, senha `prof123`
   - **Aluno**: usuário `aluno`, senha `aluno123`

## Estrutura de Arquivos

```
/
├── config/
│   └── database.php          # Configuração do banco de dados
├── classes/
│   ├── User.php             # Classe para gerenciar usuários
│   ├── Course.php           # Classe para gerenciar cursos
│   ├── Enrollment.php       # Classe para gerenciar inscrições
│   └── Lesson.php           # Classe para gerenciar aulas
├── includes/
│   ├── auth.php             # Funções de autenticação
│   ├── header.php           # Cabeçalho comum
│   └── footer.php           # Rodapé comum
├── index.php                # Página inicial
├── login.php                # Página de login
├── register.php             # Página de cadastro
├── dashboard.php            # Dashboard principal
├── my_courses.php           # Meus cursos
├── available_courses.php    # Cursos disponíveis
├── view_course.php          # Visualizar curso
├── create_course.php        # Criar curso
├── course_lessons.php       # Gerenciar aulas
├── create_lesson.php        # Criar aula
├── view_lesson.php          # Visualizar aula
├── manage_users.php         # Gerenciar usuários (admin)
├── profile.php              # Perfil do usuário
├── access_denied.php        # Página de acesso negado
└── logout.php               # Logout
```

## Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.1.3
- **Ícones**: Font Awesome 6.0
- **Arquitetura**: MVC simplificado com classes PHP

## Segurança

- Validação de entrada em todos os formulários
- Proteção contra SQL Injection com PDO prepared statements
- Sistema de controle de acesso baseado em sessões
- Sanitização de dados de saída
- Verificação de permissões em cada página

## Funcionalidades Avançadas

- **Sistema de busca** de cursos
- **Controle de progresso** nas aulas
- **Navegação sequencial** entre aulas
- **Preview de conteúdo** em tempo real
- **Interface responsiva** para mobile
- **Sistema de badges** para status

## Personalização

O sistema foi desenvolvido de forma modular, permitindo fácil personalização:

- Modificar estilos no CSS
- Adicionar novos campos no banco de dados
- Estender funcionalidades das classes
- Customizar a interface conforme necessário

## Suporte

Sistema desenvolvido em português brasileiro com interface totalmente localizada e documentação completa.

---

**Desenvolvido para atender às necessidades de instituições de ensino que precisam de uma plataforma EAD robusta e fácil de usar.**