# DecorArt — site + assistente Vitória

> Desenvolvido pela **RSFBINFORMATICA** para a DecorArt (montagem de festas infantis).

Este repositório contém **todo o site da DecorArt e a assistente virtual Vitória**, em versão pública de demonstração. É a fonte de verdade do que vai no ar.

---

## 📌 O que é este projeto

A **DecorArt** monta **festas de crianças** (aniversários e comemorações). Para atrair e atender clientes pela internet, montamos:

1. **Um site de demonstração** onde o visitante monta sua festa (escolhe tema, número de convidados e itens) e vê uma **estimativa de valor**.
2. **A Vitória**, uma assistente de IA que conversa com o visitante no chat do site: ajuda a planejar a festa, orienta o uso do montador e **coleta os dados do agendamento** (data, horário, nº de convidados, local) para a equipe confirmar depois.
3. **Um botão de WhatsApp**: quando o cliente quer falar com uma pessoa de verdade, ele é encaminhado direto para o **WhatsApp oficial da DecorArt** `wa.me/5521974431065`.

> ⚠️ **Importante:** os valores exibidos no site **são demonstrativos** (estimativas). A equipe da DecorArt confirma o orçamento e a disponibilidade reais. O site e a Vitória **não fecham reserva nem cobram** — apenas coletam o interesse e encaminham.

---

## 🌐 Onde está publicado (demo pública)

A demonstração está no **GitHub Pages**:

**🔗 https://rsfbinformatica-n3.github.io/decorart-demo/**

Você pode abrir em qualquer navegador (celular ou computador). O site completo também roda no **WordPress privado** da cliente, no servidor.

---

## 🧠 Como funciona a Vitória (a IA do chat)

- Ela roda **isolada** em um servidor próprio (**OpenClaw**), não no navegador.
- O navegador fala com uma **ponte pública** — nunca tem acesso direto ao motor da IA nem a dados internos.
- Modelo de IA: **`deepseek-chat`** (via OpenRouter) — barato e bom em português.
- A Vitória entende o **contacto WhatsApp oficial** da DecorArt e, no momento certo, **mostra ao cliente um botão verde "Falar no WhatsApp"** que abre `wa.me/5521974431065`.

---

## 🗂️ O que tem dentro do repositório

```
docs/                          → o site estático (o que o GitHub Pages publica)
├── index.html                 → página principal
├── assets/css/site.css        → estilos
├── assets/js/builder.js       → o "montador de festa" interativo
└── assets/js/chat-widget.js   → o widget do chat da Vitória (inclui botão WhatsApp)

wordpress/theme/decorart/      → tema WordPress (a versão "privada" do site)
wordpress/plugin/decorart-core → plugin de catálogo administrável

scripts/                       → auditorias e validação de publicação
README.md                      → este arquivo
```

**Duas versões do mesmo site:**
- `docs/` → a **demo pública** no GitHub Pages (aberta para qualquer um).
- `wordpress/` → o **site real** da cliente, rodando no servidor dela (acesso restrito).

---

## ✅ O que já está pronto

- [x] Site com montador interativo de festas
- [x] Estimativa de valor em tempo real
- [x] Assistente Vitória no chat (collection de agendamento + orientação)
- [x] Encaminhamento para o WhatsApp oficial da DecorArt
- [x] Catálogo administrável (plugin WordPress)
- [x] Demo pública no GitHub Pages
- [x] Crédito "Desenvolvido pela RSFBINFORMATICA" no rodapé

---

## ▶️ Como rodar a demo localmente

```bash
python3 -m http.server 8080 --directory docs
```

Abra `http://127.0.0.1:8080/` no navegador.

> O site funciona normalmente. A única limitação é o **chat**: a ponte da Vitória aceita somente as origens autorizadas (a demo pública e o site real), então o chat pode não responder em localhost/outros domínios — o resto do site funciona.

---

## 🚀 Como publicar uma atualização (fluxo seguro)

1. Altere os arquivos em **`docs/`** (demo) e/ou **`wordpress/`** (site real).
2. **Commite de forma explícita** (liste os arquivos — nunca `git add .`):
   ```bash
   git add docs/index.html docs/assets/css/site.css
   git commit -m "descrição da mudança"
   ```
3. **Mande para o GitHub** (a VM não tem credencial; o push é feito pelo controller):
   ```bash
   git fetch
   git push origin main
   ```
4. O **GitHub Pages** publica automaticamente em poucos minutos.
5. Para o **site WordPress real**, o deploy é feito no servidor (com rollback).

---

## 🔒 Segurança — o que NÃO está aqui

Este repositório é **público**, então **nenhum segredo** entra nele:

- ✗ Nenhuma chave de API (OpenRouter, OpenAI, etc.)
- ✗ Nenhum token ou senha
- ✗ Nenhum banco de dados, log ou backup
- ✗ Nenhuma credencial de servidor

O que é privado (chaves, token do gateway, banco do WordPress, ponte do chat) fica **somente no servidor**, nunca no GitHub.

---

## 📬 Contato da DecorArt

- **WhatsApp oficial:** (21) 97443-1065 → `wa.me/5521974431065`

---

## 🧾 Licença

MIT. A marca e a logo **DecorArt** pertencem aos seus respectivos titulares.