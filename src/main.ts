/**
 * Task Manager Frontend - Read-only Display
 *
 * Google Sheets との連携表示
 * - 60秒毎に自動更新
 * - スプレッドシート側でのみ編集可能
 */

import type { Task, StatusColor } from './types'

// ============================================
// 設定
// ============================================

const API_BASE_URL = 'https://example.com/app/api'
const POLL_INTERVAL = 60000 // 60秒

const STATUS_COLORS: StatusColor = {
  '未着手': 'unstarted',
  '対応中': 'working',
  '完了': 'completed'
}

// ============================================
// API通信
// ============================================

async function fetchTasks(): Promise<Task[]> {
  try {
    const timestamp = new Date().getTime()
    const response = await fetch(API_BASE_URL + '/tasks-list.php?t=' + timestamp, {
      cache: 'no-store'
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    if (!data.success) {
      throw new Error(data.error || 'Unknown error')
    }

    return data.tasks || []
  } catch (error) {
    console.error('Failed to fetch tasks:', error)
    throw error
  }
}

// ============================================
// DOM操作
// ============================================

function updateLastUpdate(): void {
  const now = new Date()
  const timeStr = now.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' })
  const lastUpdateEl = document.getElementById('last-update')
  if (lastUpdateEl) {
    lastUpdateEl.textContent = `更新: ${timeStr}`
  }
}

function renderTasks(tasks: Task[]): void {
  const container = document.getElementById('tasks-container')
  if (!container) return

  if (tasks.length === 0) {
    container.innerHTML = '<div class="no-tasks">タスクがありません</div>'
    return
  }

  container.innerHTML = tasks.map(task => createTaskCard(task)).join('')
}

function getStatusClass(status: string): string {
  return STATUS_COLORS[status] || 'unstarted'
}

function createTaskCard(task: Task): string {
  const statusClass = getStatusClass(task.status)
  const progressPercent = Math.min(Math.max(task.progress, 0), 100)

  let urlHtml = ''
  if (task.article_url && task.article_url.trim()) {
    urlHtml = `
      <div class="task-url">
        <a href="${escapeHtml(task.article_url)}" target="_blank" rel="noopener noreferrer" class="url-link">
          ${escapeHtml(task.article_url)}
        </a>
      </div>
    `
  }

  let commentHtml = ''
  if (task.comment && task.comment.trim()) {
    commentHtml = `
      <div class="task-comment">
        ${escapeHtml(task.comment)}
      </div>
    `
  }

  return `
    <div class="task-card" data-sheet-id="${task.sheet_id}">
      <div class="task-title-line">
        <span class="task-datetime">${escapeHtml(task.timestamp)}</span>
        <span class="status-inline status-${statusClass}">${escapeHtml(task.status)}</span>
        <span class="task-progress">進捗 ${progressPercent}%</span>
        <span class="task-attrs">
          年齢: ${escapeHtml(task.age)}
          デバイス: ${escapeHtml(task.device)}
          時期: ${escapeHtml(task.issue_time)}
          スキル: ${escapeHtml(task.skill_level)}
        </span>
      </div>

      <div class="task-question">${escapeHtml(task.question)}</div>

      ${urlHtml}
      ${commentHtml}
    </div>
  `
}

function escapeHtml(text: string): string {
  const map: { [key: string]: string } = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }
  return text.replace(/[&<>"']/g, char => map[char])
}

function showError(message: string): void {
  const container = document.getElementById('tasks-container')
  if (!container) return

  container.innerHTML = `<div class="error">エラー: ${escapeHtml(message)}</div>`
}

// ============================================
// アプリケーション初期化
// ============================================

async function loadAndRender(): Promise<void> {
  try {
    const tasks = await fetchTasks()
    renderTasks(tasks)
    updateLastUpdate()
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : 'Unknown error'
    showError(errorMsg)
    console.error('Load error:', error)
  }
}

function startPolling(): void {
  // 初回ロード
  loadAndRender()

  // 定期的にポーリング
  setInterval(loadAndRender, POLL_INTERVAL)
}

// ============================================
// エントリーポイント
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  startPolling()
})
