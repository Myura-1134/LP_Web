/**
 * V-Link Platform - Main JavaScript
 */

const API_BASE = '/v-link/api';

/**
 * API呼び出し
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    };
    
    if (data && method !== 'GET') {
        options.body = new URLSearchParams(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'API call failed');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * 大会一覧を取得
 */
async function loadTournaments(filters = {}) {
    try {
        const params = new URLSearchParams({ action: 'list', ...filters });
        const result = await apiCall(`${API_BASE}/tournaments.php?${params}`);
        return result.data.tournaments;
    } catch (error) {
        showError('大会の読み込みに失敗しました');
        return [];
    }
}

/**
 * 大会詳細を取得
 */
async function getTournament(id) {
    try {
        const result = await apiCall(`${API_BASE}/tournaments.php?action=get&id=${id}`);
        return result.data.tournament;
    } catch (error) {
        showError('大会の読み込みに失敗しました');
        return null;
    }
}

/**
 * 大会を作成
 */
async function createTournament(formData) {
    try {
        const result = await apiCall(
            `${API_BASE}/tournaments.php?action=create`,
            'POST',
            formData
        );
        showSuccess('大会を作成しました');
        return result.data.tournament_id;
    } catch (error) {
        showError(error.message);
        return null;
    }
}

/**
 * エントリーを作成
 */
async function createEntry(tournamentId, entryType, teamId = null) {
    try {
        const data = {
            tournament_id: tournamentId,
            entry_type: entryType
        };
        
        if (teamId) {
            data.team_id = teamId;
        }
        
        const result = await apiCall(
            `${API_BASE}/entries.php?action=create`,
            'POST',
            data
        );
        
        showSuccess('エントリーしました');
        return result.data.entry_id;
    } catch (error) {
        showError(error.message);
        return null;
    }
}

/**
 * チームを作成
 */
async function createTeam(name, description = '', discordServerId = '') {
    try {
        const result = await apiCall(
            `${API_BASE}/teams.php?action=create`,
            'POST',
            {
                name: name,
                description: description,
                discord_server_id: discordServerId
            }
        );
        
        showSuccess('チームを作成しました');
        return result.data.team_id;
    } catch (error) {
        showError(error.message);
        return null;
    }
}

/**
 * ユーザーを登録
 */
async function registerUser(username, email, password, passwordConfirm, userType = 'participant') {
    try {
        const result = await apiCall(
            `${API_BASE}/users.php?action=register`,
            'POST',
            {
                username: username,
                email: email,
                password: password,
                password_confirm: passwordConfirm,
                user_type: userType
            }
        );
        
        showSuccess('登録しました');
        return result.data.user_id;
    } catch (error) {
        showError(error.message);
        return null;
    }
}

/**
 * ユーザーをログイン
 */
async function loginUser(username, password) {
    try {
        const result = await apiCall(
            `${API_BASE}/users.php?action=login`,
            'POST',
            {
                username: username,
                password: password
            }
        );
        
        showSuccess('ログインしました');
        return result.data.user;
    } catch (error) {
        showError(error.message);
        return null;
    }
}

/**
 * 現在のユーザーを取得
 */
async function getCurrentUser() {
    try {
        const result = await apiCall(`${API_BASE}/users.php?action=get_current`);
        return result.data.user;
    } catch (error) {
        return null;
    }
}

/**
 * ログアウト
 */
function logout() {
    window.location.href = `${API_BASE}/users.php?action=logout`;
}

/**
 * 成功メッセージを表示
 */
function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success';
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

/**
 * エラーメッセージを表示
 */
function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-error';
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

/**
 * 情報メッセージを表示
 */
function showInfo(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info';
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

/**
 * フォームをシリアライズ
 */
function serializeForm(formElement) {
    const formData = new FormData(formElement);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    return data;
}

/**
 * 日時をフォーマット
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ja-JP');
}

/**
 * 日付をフォーマット
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ja-JP');
}

/**
 * ページロード時の初期化
 */
document.addEventListener('DOMContentLoaded', async () => {
    // ユーザー情報を確認
    const user = await getCurrentUser();
    
    if (user) {
        // ログイン状態の場合、ナビゲーションを更新
        updateNavigation(user);
    }
});

/**
 * ナビゲーションを更新
 */
function updateNavigation(user) {
    const navUser = document.querySelector('.nav-user');
    if (navUser) {
        navUser.innerHTML = `
            <span>${user.username}</span>
            <a href="/v-link/public/dashboard.php">ダッシュボード</a>
            <button onclick="logout()">ログアウト</button>
        `;
    }
}

/**
 * URLパラメータを取得
 */
function getUrlParameter(name) {
    const url = new URL(window.location);
    return url.searchParams.get(name);
}

/**
 * URLパラメータを設定
 */
function setUrlParameter(name, value) {
    const url = new URL(window.location);
    url.searchParams.set(name, value);
    window.history.pushState({}, '', url);
}
