const fetch = globalThis.fetch;

async function runTests() {
  const originHeaders = { 'Origin': 'http://api.vocavault.ahmedmagdy.cloud', 'Accept': 'application/json', 'Content-Type': 'application/json' };
  
  // Get CSRF
  const csrfRes = await fetch('http://api.vocavault.ahmedmagdy.cloud/sanctum/csrf-cookie', { headers: originHeaders });
  const cookies = csrfRes.headers.getSetCookie();
  let cookieStr = cookies.map(c => c.split(';')[0]).join('; ');
  let xsrfToken = decodeURIComponent(cookies.find(c => c.startsWith('XSRF-TOKEN=')).split(';')[0].split('=')[1]);

  const getHeaders = () => ({ ...originHeaders, 'Cookie': cookieStr, 'X-XSRF-TOKEN': xsrfToken, 'Referer': 'http://localhost:3000/' });

  const updateCookies = (res) => {
      const newCookies = res.headers.getSetCookie();
      if (newCookies.length > 0) {
          const m = {};
          cookieStr.split('; ').forEach(c => m[c.split('=')[0]] = c);
          newCookies.forEach(c => m[c.split('=')[0]] = c.split(';')[0]);
          cookieStr = Object.values(m).join('; ');
          const xsrf = newCookies.find(c => c.startsWith('XSRF-TOKEN='));
          if (xsrf) xsrfToken = decodeURIComponent(xsrf.split(';')[0].split('=')[1]);
      }
  };

  // Login
  const loginRes = await fetch('http://api.vocavault.ahmedmagdy.cloud/api/login', { 
    method: 'POST', 
    headers: getHeaders(), 
    body: JSON.stringify({ email: 'test@example.com', password: 'password' }) 
  });
  updateCookies(loginRes);
  console.log('Login:', loginRes.status);

  // Get Categories
  const catRes = await fetch('http://api.vocavault.ahmedmagdy.cloud/api/categories', { method: 'GET', headers: getHeaders() });
  const cats = await catRes.json();
  console.log('Categories:', cats.data.length);
  const categoryId = cats.data[0].id;

  // Create Video
  const createVidRes = await fetch('http://api.vocavault.ahmedmagdy.cloud/api/videos', { 
    method: 'POST', headers: getHeaders(), 
    body: JSON.stringify({ title: 'Test Video', url: 'https://youtube.com/watch?v=123', platform: 'youtube' }) 
  });
  const vid = await createVidRes.json();
  console.log('Create Video:', createVidRes.status, vid.data?.id);

  // Edit Video
  const editVidRes = await fetch(`http://api.vocavault.ahmedmagdy.cloud/api/videos/${vid.data.id}`, { 
    method: 'PUT', headers: getHeaders(), 
    body: JSON.stringify({ title: 'Updated Video', url: 'https://youtube.com/watch?v=123', platform: 'youtube' }) 
  });
  console.log('Edit Video:', editVidRes.status);

  // Create Word
  const createWordRes = await fetch('http://api.vocavault.ahmedmagdy.cloud/api/words', { 
    method: 'POST', headers: getHeaders(), 
    body: JSON.stringify({ word: 'Hello', meaning: 'Greeting', category_id: categoryId, video_ids: [vid.data.id] }) 
  });
  const word = await createWordRes.json();
  console.log('Create Word:', createWordRes.status, word.data?.id);

  // Delete Video
  const delVidRes = await fetch(`http://api.vocavault.ahmedmagdy.cloud/api/videos/${vid.data.id}`, { method: 'DELETE', headers: getHeaders() });
  console.log('Delete Video:', delVidRes.status);
}

runTests().catch(err => {
  console.error('Test failed:', err);
  process.exit(1);
});
