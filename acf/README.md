# ACF Structure

## Companies
- setor-emp
- endereco-emp
- cidade-emp
- pais-emp
- e-mail-emp
- estado-emp
- representante-emp
- grupo-emp
- relac-emp
- produtos-emp
- responsavel-emp
- soluc-emp *(sw, hw, servicos — same underlying meta_key, used as a group)*
- agente-emp
- geracao-emp *(tipo_ger)*

## Contacts
- email-ctt
- sexo-ctt
- endereco-ctt
- cidade-ctt
- pais-ctt
- estado-ctt
- forum-ctt
- waf-ctt
- palestrante-waf-ctt
- pesquisa-ctt
- academy-ctt
- anos-waf-ctt
- anos-palestr-ctt
- tipo-pesquisa-ctt
- quais-webinares-ctt
- empresa-ctt
- departamento-ctt
- cargo-ctt
- agente-emp *(relational field — looks up Companies)*
