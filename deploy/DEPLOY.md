# DecorArt WordPress — deploy na VPS

Stack: WordPress (Apache/PHP 8.3) + MySQL 8 via Docker Compose, com o tema
`decorart` e o plugin `decorart-core` montados direto do repositório.

## Requisitos (VPS)
- Docker + Docker Compose
- Caddy (ou outro proxy reverso) apontando o domínio p/ o WP (porta 8099, loopback)

## 1. Subir
```bash
cd deploy
cp .env.example .env    # edite com senhas fortes
docker compose up -d
```

## 2. Instalar o WordPress (primeira vez)
Acesse `https://<dominio>/wp-admin` e conclua a instalação, **ou** via wp-cli:
```bash
docker exec decorart-wp sh -c "
  cd /var/www/html
  wp core install --url='https://<dominio>' --title='DecorArt' \
    --admin_user='<admin>' --admin_password='<senha>' --admin_email='<email>' --allow-root
  wp plugin activate decorart-core --allow-root
"
```
O MySQL pode levar ~30s no primeiro boot.

## 3. Área administrativa
- Login: `https://<dominio>/wp-admin` (protegido por senha nativa do WP)
- **Vendas & Eventos** (CPT `da_evento`): planilha de vendas (Data, Cliente, Valor, Status)
- **Calendário**: submenu → calendário de rotina mensal (blocos coloridos por status)

## 4. Caddy (proxy reverso, HTTP no loopback)
```caddy
<dominio> {
    reverse_proxy 127.0.0.1:8099
}
```
> Atenção: o WordPress com proxy+HTTP exige definir o siteurl como http/URL real
> antes de logar (`wp option update siteurl/home`), senão redireciona p/ HTTPS inexistente.

## 5. Backups
```bash
# banco (diário)
docker exec decorart-db sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" decorart' > backup.sql
# wp-content (uploads/temas/plugins)
docker run --rm -v decorart_wp_data:/d -v $PWD:/b alpine tar czf /b/wp-data.tar.gz -C /d .
```

## Portas
WP/MySQL escutam **somente em loopback**; o Caddy é a única entrada pública (80/443).