(()=>{
  const API='https://decor.taila4e8af.ts.net:8443/api/chat';
  const SESSION_KEY='decorart_vitoria_session_v1';
  const HISTORY_KEY='decorart_vitoria_history_v1';
  const greeting='Oi! Eu sou a Vitória 🎈 Posso ajudar a organizar sua festa. Para começar, qual tipo de comemoração você está planejando?';
  const uuid=()=>globalThis.crypto?.randomUUID?.()||'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,c=>{const r=Math.random()*16|0,v=c==='x'?r:(r&3|8);return v.toString(16)});
  const safeGet=(key,fallback)=>{try{return localStorage.getItem(key)||fallback}catch{return fallback}};
  const safeSet=(key,value)=>{try{localStorage.setItem(key,value)}catch{}};
  let sessionId=safeGet(SESSION_KEY,'');
  if(!/^[a-f0-9-]{36}$/i.test(sessionId)){sessionId=uuid();safeSet(SESSION_KEY,sessionId)}
  let history=[];
  try{history=JSON.parse(safeGet(HISTORY_KEY,'[]'));if(!Array.isArray(history))history=[]}catch{history=[]}
  if(!history.length)history=[{role:'assistant',text:greeting}];

  const root=document.createElement('div');
  root.className='da-chat';
  root.innerHTML=`
    <button class="da-chat-launcher" type="button" aria-label="Conversar com Vitória" aria-expanded="false" aria-controls="da-chat-panel">
      <span class="da-chat-launcher-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M6.5 5.5h19a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H15l-6.6 4.2 1.4-4.2H6.5a3 3 0 0 1-3-3v-11a3 3 0 0 1 3-3Z"/><circle cx="11" cy="14" r="1.5"/><circle cx="16" cy="14" r="1.5"/><circle cx="21" cy="14" r="1.5"/></svg></span>
      <span class="da-chat-launcher-close" aria-hidden="true">×</span>
      <span class="da-chat-launcher-label">Fale com a Vitória</span>
    </button>
    <section class="da-chat-panel" id="da-chat-panel" role="dialog" aria-label="Chat com Vitória" aria-hidden="true">
      <header class="da-chat-head">
        <div class="da-chat-avatar" aria-hidden="true">V</div>
        <div><strong>Vitória</strong><span><i></i> Assistente virtual DecorArt</span></div>
        <button class="da-chat-reset" type="button" title="Iniciar nova conversa" aria-label="Iniciar nova conversa"><svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 1 0 2.34-5.66L4 8.68M4 4v4.68h4.68"/></svg></button>
        <button class="da-chat-close" type="button" aria-label="Fechar conversa">×</button>
      </header>
      <div class="da-chat-notice">Atendimento por IA. Itens e valores do protótipo são demonstrativos.</div>
      <div class="da-chat-messages" role="log" aria-live="polite" aria-relevant="additions"></div>
      <div class="da-chat-typing" hidden><span></span><span></span><span></span><small>Vitória está digitando</small></div>
      <form class="da-chat-form">
        <label class="sr-only" for="da-chat-input">Sua mensagem</label>
        <textarea id="da-chat-input" rows="1" maxlength="1200" placeholder="Escreva sua mensagem…" required></textarea>
        <button type="submit" aria-label="Enviar mensagem"><svg viewBox="0 0 24 24"><path d="m3 11 17-8-6.5 18-2.5-7-8-3Zm8 3 9-11"/></svg></button>
      </form>
      <footer class="da-chat-foot">Não envie senhas, documentos ou dados de pagamento.</footer>
    </section>`;
  document.body.appendChild(root);

  const launcher=root.querySelector('.da-chat-launcher');
  const panel=root.querySelector('.da-chat-panel');
  const close=root.querySelector('.da-chat-close');
  const reset=root.querySelector('.da-chat-reset');
  const messages=root.querySelector('.da-chat-messages');
  const typing=root.querySelector('.da-chat-typing');
  const form=root.querySelector('.da-chat-form');
  const input=root.querySelector('textarea');
  const submit=form.querySelector('button');
  let busy=false;

  function persist(){safeSet(HISTORY_KEY,JSON.stringify(history.slice(-30)))}
  function addMessage(role,text,save=true){
    const article=document.createElement('article');article.className=`da-chat-message ${role}`;
    const bubble=document.createElement('div');bubble.textContent=text;article.appendChild(bubble);
    const label=document.createElement('small');label.textContent=role==='assistant'?'Vitória':'Você';article.appendChild(label);
    messages.appendChild(article);messages.scrollTop=messages.scrollHeight;
    if(save){history.push({role,text});persist()}
  }
  function render(){messages.textContent='';history.slice(-30).forEach(m=>addMessage(m.role,m.text,false));messages.scrollTop=messages.scrollHeight}
  function toggle(force){
    const open=typeof force==='boolean'?force:!root.classList.contains('open');
    root.classList.toggle('open',open);launcher.setAttribute('aria-expanded',String(open));panel.setAttribute('aria-hidden',String(!open));
    if(open){render();setTimeout(()=>input.focus(),150)}
  }
  function setBusy(value){busy=value;typing.hidden=!value;submit.disabled=value;input.disabled=value;if(value){messages.scrollTop=messages.scrollHeight}}
  async function send(message){
    addMessage('user',message);setBusy(true);
    try{
      const response=await fetch(API,{method:'POST',mode:'cors',credentials:'omit',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId,message})});
      const data=await response.json().catch(()=>({}));
      if(!response.ok)throw new Error(data.message||'A Vitória está indisponível no momento.');
      addMessage('assistant',String(data.reply||'Não consegui responder agora. Tente novamente.'));
    }catch(error){addMessage('system',error.message||'Não foi possível conectar. Tente novamente em instantes.')}
    finally{setBusy(false);input.focus()}
  }

  launcher.addEventListener('click',()=>toggle());close.addEventListener('click',()=>toggle(false));
  reset.addEventListener('click',()=>{if(busy)return;sessionId=uuid();safeSet(SESSION_KEY,sessionId);history=[{role:'assistant',text:greeting}];persist();render();input.focus()});
  form.addEventListener('submit',event=>{event.preventDefault();const value=input.value.trim();if(!value||busy)return;input.value='';input.style.height='auto';send(value)});
  input.addEventListener('input',()=>{input.style.height='auto';input.style.height=`${Math.min(input.scrollHeight,110)}px`});
  input.addEventListener('keydown',event=>{if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();form.requestSubmit()}});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&root.classList.contains('open'))toggle(false)});
  render();
})();
