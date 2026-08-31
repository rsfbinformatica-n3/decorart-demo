# DecorArt — protótipo interativo

Demonstração pública de uma experiência de montagem de festas para a DecorArt, desenvolvida pela RSFBINFORMATICA.

## Escopo

- catálogo por categorias;
- quantidade de convidados;
- referência de estações;
- seleção e quantidade de itens;
- pré-visualização conceitual com itens arrastáveis;
- estimativa dinâmica;
- resumo copiável para iniciar o atendimento;
- chat com a assistente virtual Vitória;
- tema WordPress e plugin de catálogo administrável;
- versão estática compatível com GitHub Pages.

## Assistente Vitória

O balão **Fale com a Vitória** conecta a demonstração a uma assistente de IA isolada. Ela ajuda o visitante a organizar informações da festa, faz uma pergunta por vez e não confirma preço, disponibilidade, reserva ou orçamento final.

As mensagens são processadas por um modelo de IA e podem permanecer no histórico operacional do atendimento. Não envie senhas, documentos, dados de pagamento ou outras informações sensíveis.

## Aviso comercial

Os produtos, categorias e valores desta demonstração são **ilustrativos**. Eles não representam disponibilidade, preço, medida ou condição comercial oficial da DecorArt. Antes de uma publicação comercial, o catálogo deverá ser substituído por dados fornecidos e aprovados pela cliente.

## Estrutura

- `docs/` — demonstração estática servida pelo GitHub Pages;
- `wordpress/theme/decorart/` — tema WordPress;
- `wordpress/plugin/decorart-core/` — tipo de conteúdo administrável do catálogo.

## Executar a demonstração

```bash
python3 -m http.server 8080 --directory docs
```

Acesse `http://127.0.0.1:8080/`.

A interface local pode ser testada, mas a API do chat aceita somente as origens autorizadas da demonstração e do staging.

## WordPress

1. Copie o tema para `wp-content/themes/decorart/`.
2. Copie o plugin para `wp-content/plugins/decorart-core/`.
3. Ative o plugin e o tema no painel.
4. Cadastre os itens reais no menu **Itens da festa** antes do uso comercial.

## Segurança e dados

Segredos, banco de dados, uploads, credenciais, logs, backups e arquivos de runtime não fazem parte deste repositório. O token do gateway permanece somente no servidor; o navegador acessa uma ponte pública limitada, sem acesso direto ao OpenClaw administrativo.

## Licença

MIT. A marca e a logo DecorArt permanecem pertencentes aos seus respectivos titulares.
