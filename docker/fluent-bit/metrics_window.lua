local WINDOW_SECONDS = 60
local HEARTBEAT_RESULT = "__heartbeat__"
local timestamps = {}

local function cleanup(now)
    local cutoff = now - WINDOW_SECONDS
    local new_queue = {}
    for _, t in ipairs(timestamps) do
        if t >= cutoff then
            table.insert(new_queue, t)
        end
    end
    timestamps = new_queue
end

function count_per_minute(tag, timestamp, record)
    local now = os.time()
    local result = record.result

    if result ~= HEARTBEAT_RESULT then
        table.insert(timestamps, now)
    end

    cleanup(now)
    record.per_minute_count = #timestamps

    return 2, timestamp, record
end
