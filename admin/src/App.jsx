import { useCallback, useEffect, useMemo, useState } from 'react';

const API_BASE = import.meta.env.VITE_API_BASE_URL || '';

async function request(path, token, options = {}) {
  const headers = { Authorization: `Bearer ${token}`, ...(options.headers || {}) };
  if (options.body && !(options.body instanceof FormData)) headers['Content-Type'] = 'application/json';
  const response = await fetch(`${API_BASE}/api/v1${path}`, { ...options, headers });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(payload.error || `Request failed (${response.status})`);
  return payload.data;
}

function slugify(value) {
  return value.toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function Field({ label, children }) {
  return <label className="field"><span>{label}</span>{children}</label>;
}

function App() {
  const [token, setToken] = useState('');
  const [connected, setConnected] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [data, setData] = useState({ families: [], instruments: [], libraries: [], samples: [], sources: [], licenses: [] });
  const [family, setFamily] = useState({ name: '', slug: '', category: 'eastern' });
  const [instrument, setInstrument] = useState({ name: '', slug: '', familyId: '', soundType: 'acoustic' });
  const [library, setLibrary] = useState({ name: '', slug: '', description: '', status: 'published', visibility: 'public' });
  const [source, setSource] = useState({ name: '', sourceUrl: '', attributionText: '' });
  const [license, setLicense] = useState({ name: '', identifier: '', termsUrl: '', allowsRedistribution: false, attributionRequired: true });
  const [upload, setUpload] = useState({ file: null, name: '', instrumentId: '', libraryId: '', licenseId: '', sourceId: '', midiNote: '', octave: '', velocityMin: '', velocityMax: '', rootNote: '', sampleRate: '', durationMs: '', articulation: '' });

  const refresh = useCallback(async (activeToken) => {
    const [families, instruments, libraries, samples, sources, licenses] = await Promise.all([
      request('/families?limit=100', activeToken),
      request('/instruments?limit=100', activeToken),
      request('/admin/libraries?limit=100', activeToken),
      request('/admin/samples?limit=100', activeToken),
      request('/admin/sources', activeToken),
      request('/admin/licenses', activeToken),
    ]);
    setData({ families, instruments, libraries, samples, sources, licenses });
  }, []);

  useEffect(() => {
    const saved = sessionStorage.getItem('tonebase_admin_token');
    if (saved) {
      setToken(saved);
      refresh(saved).then(() => setConnected(true)).catch(() => sessionStorage.removeItem('tonebase_admin_token'));
    }
  }, [refresh]);

  const run = async (action, successMessage) => {
    setBusy(true); setError(''); setNotice('');
    try {
      await action();
      setNotice(successMessage);
      await refresh(token);
    } catch (exception) {
      setError(exception.message || 'Something went wrong.');
    } finally {
      setBusy(false);
    }
  };

  const connect = async (event) => {
    event.preventDefault();
    setBusy(true); setError('');
    try {
      await refresh(token);
      sessionStorage.setItem('tonebase_admin_token', token);
      setConnected(true);
      setNotice('Connected to ToneBase API.');
    } catch (exception) {
      setError(exception.message);
      setConnected(false);
    } finally {
      setBusy(false);
    }
  };

  const create = (path, payload) => request(path, token, { method: 'POST', body: JSON.stringify(payload) });

  const uploadSample = async (event) => {
    event.preventDefault();
    if (!upload.file) throw new Error('Choose an audio file first.');
    const form = new FormData();
    form.append('file', upload.file);
    const asset = await request('/admin/uploads', token, { method: 'POST', body: form });
    const numberOrNull = (value) => value === '' ? null : Number(value);
    await create('/admin/samples', {
      name: upload.name || upload.file.name,
      instrumentId: Number(upload.instrumentId),
      libraryId: Number(upload.libraryId),
      licenseId: Number(upload.licenseId),
      sourceId: Number(upload.sourceId),
      ...asset,
      midiNote: numberOrNull(upload.midiNote),
      octave: numberOrNull(upload.octave),
      velocityMin: numberOrNull(upload.velocityMin),
      velocityMax: numberOrNull(upload.velocityMax),
      rootNote: numberOrNull(upload.rootNote),
      sampleRate: numberOrNull(upload.sampleRate),
      durationMs: numberOrNull(upload.durationMs),
      articulation: upload.articulation || null,
    });
    setUpload({ file: null, name: '', instrumentId: '', libraryId: '', licenseId: '', sourceId: '', midiNote: '', octave: '', velocityMin: '', velocityMax: '', rootNote: '', sampleRate: '', durationMs: '', articulation: '' });
    event.target.reset();
  };

  const pendingCount = useMemo(() => data.samples.filter((sample) => sample.status === 'pending_review').length, [data.samples]);

  if (!connected) {
    return (
      <main className="login-shell">
        <section className="login-card">
          <div className="brand-mark">T</div>
          <p className="eyebrow">SOUND LIBRARY MANAGEMENT</p>
          <h1>ToneBase Console</h1>
          <p className="muted">Connect to your ToneBase API using the administrator token configured on the server.</p>
          <form onSubmit={connect}>
            <Field label="Admin API token">
              <input type="password" value={token} onChange={(event) => setToken(event.target.value)} placeholder="Paste API_ADMIN_TOKEN" required />
            </Field>
            {error && <p className="alert error">{error}</p>}
            <button className="primary full" disabled={busy}>{busy ? 'Connecting…' : 'Connect to API'}</button>
          </form>
          <p className="fine-print">The token is kept in this browser tab session only.</p>
        </section>
      </main>
    );
  }

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand"><div className="brand-mark">T</div><div><strong>ToneBase</strong><small>ADMIN CONSOLE</small></div></div>
        <nav>
          <a href="#overview" className="active">Overview</a>
          <a href="#catalog">Instrument catalog</a>
          <a href="#libraries">Sound libraries</a>
          <a href="#assets">Audio assets</a>
          <a href="#review">Review queue <b>{pendingCount}</b></a>
        </nav>
        <div className="sidebar-bottom">
          <span className="online-dot">API connected</span>
          <button className="quiet" onClick={() => { sessionStorage.removeItem('tonebase_admin_token'); setToken(''); setConnected(false); }}>Disconnect</button>
        </div>
      </aside>

      <main className="main-content">
        <header className="topbar"><div><p className="eyebrow">WORKSPACE / LIBRARY MANAGEMENT</p><h1>Overview</h1></div><button className="secondary" onClick={() => run(() => refresh(token), 'Data refreshed.')}>↻ Refresh data</button></header>
        {error && <p className="alert error">{error}</p>}
        {notice && <p className="alert success">{notice}</p>}

        <section id="overview" className="stats-grid">
          <article className="stat-card"><span>Instrument families</span><strong>{data.families.length}</strong><small>Catalog groups</small></article>
          <article className="stat-card"><span>Instruments</span><strong>{data.instruments.length}</strong><small>Available instruments</small></article>
          <article className="stat-card"><span>Sound libraries</span><strong>{data.libraries.length}</strong><small>All visibility states</small></article>
          <article className="stat-card"><span>Audio samples</span><strong>{data.samples.length}</strong><small>{pendingCount} awaiting review</small></article>
        </section>

        <section id="catalog" className="panel-grid">
          <article className="panel">
            <div className="panel-heading"><div><p className="eyebrow">CATALOG</p><h2>Add instrument family</h2></div></div>
            <form onSubmit={(event) => { event.preventDefault(); run(() => create('/admin/families', family), 'Instrument family created.'); }}>
              <Field label="Family name"><input required value={family.name} onChange={(e) => setFamily({ ...family, name: e.target.value, slug: slugify(e.target.value) })} placeholder="Eastern Strings" /></Field>
              <Field label="Slug"><input required value={family.slug} onChange={(e) => setFamily({ ...family, slug: e.target.value })} /></Field>
              <Field label="Category"><select value={family.category} onChange={(e) => setFamily({ ...family, category: e.target.value })}><option value="eastern">Eastern</option><option value="western">Western</option><option value="keyboard">Keyboard</option><option value="percussion">Percussion</option><option value="electronic">Electronic</option><option value="other">Other</option></select></Field>
              <button className="primary" disabled={busy}>Create family</button>
            </form>
          </article>

          <article className="panel">
            <div className="panel-heading"><div><p className="eyebrow">CATALOG</p><h2>Add instrument</h2></div></div>
            <form onSubmit={(event) => { event.preventDefault(); run(() => create('/admin/instruments', { ...instrument, familyId: Number(instrument.familyId) }), 'Instrument created.'); }}>
              <Field label="Instrument name"><input required value={instrument.name} onChange={(e) => setInstrument({ ...instrument, name: e.target.value, slug: slugify(e.target.value) })} placeholder="Oud" /></Field>
              <Field label="Slug"><input required value={instrument.slug} onChange={(e) => setInstrument({ ...instrument, slug: e.target.value })} /></Field>
              <Field label="Family"><select required value={instrument.familyId} onChange={(e) => setInstrument({ ...instrument, familyId: e.target.value })}><option value="">Choose family</option>{data.families.map((x) => <option key={x.id} value={x.id}>{x.name}</option>)}</select></Field>
              <Field label="Sound type"><select value={instrument.soundType} onChange={(e) => setInstrument({ ...instrument, soundType: e.target.value })}><option value="acoustic">Acoustic</option><option value="electronic">Electronic</option><option value="hybrid">Hybrid</option></select></Field>
              <button className="primary" disabled={busy}>Create instrument</button>
            </form>
          </article>
        </section>

        <section id="libraries" className="panel-grid">
          <article className="panel">
            <div className="panel-heading"><div><p className="eyebrow">LIBRARY</p><h2>Create sound library</h2></div></div>
            <form onSubmit={(event) => { event.preventDefault(); run(() => create('/admin/libraries', library), 'Sound library created.'); }}>
              <Field label="Library name"><input required value={library.name} onChange={(e) => setLibrary({ ...library, name: e.target.value, slug: slugify(e.target.value) })} placeholder="Oud Essentials" /></Field>
              <Field label="Slug"><input required value={library.slug} onChange={(e) => setLibrary({ ...library, slug: e.target.value })} /></Field>
              <Field label="Description"><textarea value={library.description} onChange={(e) => setLibrary({ ...library, description: e.target.value })} rows="2" /></Field>
              <div className="two-fields">
                <Field label="Status"><select value={library.status} onChange={(e) => setLibrary({ ...library, status: e.target.value })}><option value="draft">Draft</option><option value="published">Published</option></select></Field>
                <Field label="Visibility"><select value={library.visibility} onChange={(e) => setLibrary({ ...library, visibility: e.target.value })}><option value="private">Private</option><option value="public">Public</option></select></Field>
              </div>
              <button className="primary" disabled={busy}>Create library</button>
            </form>
          </article>

          <article className="panel">
            <div className="panel-heading"><div><p className="eyebrow">RIGHTS & ATTRIBUTION</p><h2>Register audio source</h2></div></div>
            <form onSubmit={(event) => { event.preventDefault(); run(() => create('/admin/sources', source), 'Audio source registered.'); }}>
              <Field label="Source name"><input required value={source.name} onChange={(e) => setSource({ ...source, name: e.target.value })} placeholder="Creator or sound library" /></Field>
              <Field label="Source URL"><input type="url" value={source.sourceUrl} onChange={(e) => setSource({ ...source, sourceUrl: e.target.value })} placeholder="https://" /></Field>
              <Field label="Attribution text"><textarea value={source.attributionText} onChange={(e) => setSource({ ...source, attributionText: e.target.value })} rows="2" /></Field>
              <button className="primary" disabled={busy}>Register source</button>
            </form>
          </article>

          <article className="panel">
            <div className="panel-heading"><div><p className="eyebrow">RIGHTS & ATTRIBUTION</p><h2>Register audio license</h2></div></div>
            <form onSubmit={(event) => { event.preventDefault(); run(() => create('/admin/licenses', license), 'Audio license registered.'); }}>
              <Field label="License name"><input required value={license.name} onChange={(e) => setLicense({ ...license, name: e.target.value })} placeholder="CC BY 4.0" /></Field>
              <Field label="Identifier"><input value={license.identifier} onChange={(e) => setLicense({ ...license, identifier: e.target.value })} /></Field>
              <Field label="License terms URL"><input type="url" value={license.termsUrl} onChange={(e) => setLicense({ ...license, termsUrl: e.target.value })} placeholder="https://" /></Field>
              <label className="check"><input type="checkbox" checked={license.allowsRedistribution} onChange={(e) => setLicense({ ...license, allowsRedistribution: e.target.checked })} /> Redistribution is permitted</label>
              <label className="check"><input type="checkbox" checked={license.attributionRequired} onChange={(e) => setLicense({ ...license, attributionRequired: e.target.checked })} /> Attribution is required</label>
              <button className="primary" disabled={busy}>Register license</button>
            </form>
          </article>
        </section>

        <section id="assets" className="panel">
          <div className="panel-heading"><div><p className="eyebrow">AUDIO INGESTION</p><h2>Upload audio sample</h2><p className="muted">Files are stored in the private audio volume. Each upload is verified before metadata is registered.</p></div><span className="tag">100 MB max</span></div>
          <form onSubmit={(event) => { event.preventDefault(); run(() => uploadSample(event), 'Audio sample uploaded and queued for review.'); }}>
            <div className="upload-drop"><input type="file" accept=".wav,.flac,.ogg,.mp3,.sf2,.sfz,audio/*" required onChange={(e) => setUpload({ ...upload, file: e.target.files?.[0] || null, name: e.target.files?.[0]?.name || '' })} /><span>{upload.file ? upload.file.name : 'Choose WAV, FLAC, OGG, MP3, SF2 or SFZ file'}</span></div>
            <div className="form-grid">
              <Field label="Sample name"><input required value={upload.name} onChange={(e) => setUpload({ ...upload, name: e.target.value })} /></Field>
              <Field label="Instrument"><select required value={upload.instrumentId} onChange={(e) => setUpload({ ...upload, instrumentId: e.target.value })}><option value="">Choose instrument</option>{data.instruments.map((x) => <option key={x.id} value={x.id}>{x.name}</option>)}</select></Field>
              <Field label="Sound library"><select required value={upload.libraryId} onChange={(e) => setUpload({ ...upload, libraryId: e.target.value })}><option value="">Choose library</option>{data.libraries.map((x) => <option key={x.id} value={x.id}>{x.name} ({x.status})</option>)}</select></Field>
              <Field label="Audio license"><select required value={upload.licenseId} onChange={(e) => setUpload({ ...upload, licenseId: e.target.value })}><option value="">Choose license</option>{data.licenses.map((x) => <option key={x.id} value={x.id}>{x.name}</option>)}</select></Field>
              <Field label="Audio source"><select required value={upload.sourceId} onChange={(e) => setUpload({ ...upload, sourceId: e.target.value })}><option value="">Choose source</option>{data.sources.map((x) => <option key={x.id} value={x.id}>{x.name}</option>)}</select></Field>
              <Field label="MIDI note (0–127)"><input type="number" min="0" max="127" value={upload.midiNote} onChange={(e) => setUpload({ ...upload, midiNote: e.target.value })} /></Field>
              <Field label="Octave"><input type="number" min="-2" max="10" value={upload.octave} onChange={(e) => setUpload({ ...upload, octave: e.target.value })} /></Field>
              <Field label="Velocity min"><input type="number" min="1" max="127" value={upload.velocityMin} onChange={(e) => setUpload({ ...upload, velocityMin: e.target.value })} /></Field>
              <Field label="Velocity max"><input type="number" min="1" max="127" value={upload.velocityMax} onChange={(e) => setUpload({ ...upload, velocityMax: e.target.value })} /></Field>
              <Field label="Root note"><input type="number" min="0" max="127" value={upload.rootNote} onChange={(e) => setUpload({ ...upload, rootNote: e.target.value })} /></Field>
              <Field label="Sample rate (Hz)"><input type="number" min="8000" max="384000" value={upload.sampleRate} onChange={(e) => setUpload({ ...upload, sampleRate: e.target.value })} /></Field>
              <Field label="Duration (ms)"><input type="number" min="1" value={upload.durationMs} onChange={(e) => setUpload({ ...upload, durationMs: e.target.value })} /></Field>
              <Field label="Articulation"><input value={upload.articulation} onChange={(e) => setUpload({ ...upload, articulation: e.target.value })} placeholder="sustain, pizzicato…" /></Field>
            </div>
            <button className="primary" disabled={busy || !upload.file}>{busy ? 'Uploading…' : 'Upload and register sample'}</button>
          </form>
        </section>

        <section id="review" className="panel">
          <div className="panel-heading"><div><p className="eyebrow">CONTENT REVIEW</p><h2>Audio sample review queue</h2></div><span className="tag">{pendingCount} pending</span></div>
          {data.samples.length === 0 ? <p className="empty">No audio samples registered yet.</p> : (
            <div className="table-wrap"><table><thead><tr><th>Sample</th><th>Instrument</th><th>Library</th><th>Format</th><th>Status</th><th>Review</th></tr></thead><tbody>
              {data.samples.map((sample) => <tr key={sample.id}><td><strong>{sample.name}</strong><small>#{sample.id} · {sample.fileSizeBytes} bytes</small></td><td>{sample.instrumentName}</td><td>{sample.libraryName}</td><td><span className="format">{sample.format.toUpperCase()}</span></td><td><span className={`status ${sample.status}`}>{sample.status.replace('_', ' ')}</span></td><td>{sample.status === 'pending_review' ? <div className="actions"><button className="approve" disabled={busy} onClick={() => run(() => request(`/admin/samples/${sample.id}/status`, token, { method: 'PATCH', body: JSON.stringify({ status: 'published' }) }), 'Sample published.')}>Publish</button><button className="reject" disabled={busy} onClick={() => run(() => request(`/admin/samples/${sample.id}/status`, token, { method: 'PATCH', body: JSON.stringify({ status: 'rejected' }) }), 'Sample rejected.')}>Reject</button></div> : '—'}</td></tr>)}
            </tbody></table></div>
          )}
        </section>
        <footer> ToneBase API · Version 1.0.0 · English administration console</footer>
      </main>
    </div>
  );
}

export default App;
